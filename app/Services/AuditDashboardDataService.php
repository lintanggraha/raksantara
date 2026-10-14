<?php

namespace App\Services;

use App\Risk;
use App\Support\RiskOwnerAccess;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AuditDashboardDataService
{
    private AuditDashboardImportAdapter $importAdapter;

    public function __construct(?AuditDashboardImportAdapter $importAdapter = null)
    {
        $this->importAdapter = $importAdapter ?: new AuditDashboardImportAdapter();
    }

    public function build(array $filters = []): array
    {
        $normalizedFilters = $this->normalizeFilters($filters);

        return $this->buildFromPptSnapshot($normalizedFilters);
    }

    /**
     * Import-ready pipeline. Parser file akan ditambahkan nanti,
     * tapi hasil parser cukup kirim array rows ke method ini.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function buildFromImportedRows(array $rows, array $filters = []): array
    {
        $normalizedFilters = $this->normalizeFilters($filters);
        $normalizedRows = collect($this->importAdapter->normalize($rows));

        return $this->buildFromRows(
            $normalizedRows,
            $normalizedFilters,
            $this->availableYearsFromRows($normalizedRows),
            'import'
        );
    }

    /**
     * Default dashboard source: fixed PPT snapshot.
     * Risk register tidak dipakai untuk angka dashboard audit saat ini.
     *
     * @param  array{year: int|null, bank: string|null}  $filters
     */
    private function buildFromPptSnapshot(array $filters): array
    {
        $statusColors = $this->statusColors();
        $snapshot = $this->snapshotData();

        $summaryRows = $snapshot['summary_rows'];
        $overdueRows = $snapshot['overdue_rows'];
        $rekapRows = $snapshot['rekap_rows'];
        $progressByBankRows = $snapshot['progress_by_bank'];
        $progressByRegionRows = $snapshot['progress_by_region'];
        $detailRows = $snapshot['detail_rows'];
        $mapPoints = array_map(function (array $point) {
            $coords = $this->coordinatesByBank((string) $point['bank']);

            return ['bank' => $point['bank'], 'lat' => (float) $coords['lat'], 'lng' => (float) $coords['lng']] + $point;
        }, $snapshot['map_points']);

        return [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'source' => 'snapshot',
            'period' => $snapshot['period'],
            'filters' => [
                'selected_year' => null,
                'selected_bank' => null,
                'years' => [],
                'banks' => [],
            ],
            'summary_cards' => [
                'total_temuan' => [
                    'title' => 'Total Temuan',
                    'rows' => $summaryRows,
                    'grand_total' => $this->summaryTotal($summaryRows),
                ],
                'resume_temuan' => [
                    'title' => 'Resume Temuan',
                    'rows' => $summaryRows,
                    'grand_total' => $this->summaryTotal($summaryRows),
                ],
                'temuan_jatuh_tempo' => [
                    'title' => 'Temuan Jatuh Tempo',
                    'rows' => $overdueRows,
                    'grand_total' => $this->summaryTotal($overdueRows),
                ],
            ],
            'executive_actions' => $this->executiveActions(),
            'rekap' => [
                'rows' => $rekapRows,
                'bar_chart' => $this->buildRekapBarChart($rekapRows),
            ],
            'maps' => [
                'points' => $mapPoints,
            ],
            'progress' => [
                'by_bank' => ['rows' => $progressByBankRows],
                'by_region' => ['rows' => $progressByRegionRows],
                'by_product' => ['rows' => $snapshot['progress_by_product']],
                'status_by_bank_chart' => $this->buildStatusByGroupChart($progressByBankRows, 'group'),
                'region_percentage_chart' => $this->buildStatusPercentageChart($progressByRegionRows),
            ],
            'trend' => [
                'created' => $this->buildSnapshotTrendData($detailRows, false),
                'closed' => $this->buildSnapshotTrendData($detailRows, true),
            ],
            'detail_outstanding' => [
                'rows' => $detailRows,
            ],
            'duedate_maturity' => [
                'by_bank' => $snapshot['due_by_bank'],
                'by_division' => $snapshot['due_by_division'],
                'by_product' => $snapshot['due_by_product'],
                'pie_by_bank' => $snapshot['pie_by_bank'],
                'pie_by_division' => $snapshot['pie_by_division'],
                'pie_by_product' => $snapshot['pie_by_product'],
                'grand_total' => $snapshot['due_grand_total'],
            ],
            'status_colors' => $statusColors,
        ];
    }

    /**
     * Snapshot shown before any spreadsheet is imported: the organisation's own
     * file in private/ when present, otherwise the bundled fictional sample.
     */
    private function snapshotData(): array
    {
        $path = (string) config('audit_dashboard.snapshot_path');
        if ($path === '' || !is_file($path)) {
            $path = resource_path('data/audit-dashboard-sample.php');
        }

        return require $path;
    }

    /**
     * @param  array{year: int|null, bank: string|null}  $filters
     * @param  array<int, int>  $years
     */
    private function buildFromRows(Collection $rows, array $filters, array $years, string $source): array
    {
        $rows = $rows->map(function ($row) {
            return $this->sanitizePreparedRow($row);
        })->filter(function ($row) {
            return is_array($row);
        })->values();

        $selectedYear = $filters['year'];
        $selectedBank = $this->normalizeSelectedBank($filters['bank']);

        if (!is_null($selectedYear)) {
            $rows = $rows->filter(function (array $row) use ($selectedYear) {
                return (int) ($row['year'] ?? 0) === (int) $selectedYear;
            })->values();
        }

        if (!is_null($selectedBank)) {
            $rows = $rows->filter(function (array $row) use ($selectedBank) {
                return strtoupper((string) ($row['bank'] ?? '')) === $selectedBank;
            })->values();
        }

        $statusSummary = $this->buildStatusSummary($rows);
        $overdueSummary = $this->buildOverdueSummary($rows);
        $rekapRows = $this->buildRekapRows($rows);
        $progressByBank = $this->buildPercentageMatrix($rows, 'bank');
        $progressByRegion = $this->buildPercentageMatrix($rows, 'region');
        $progressByProduct = $this->buildPercentageMatrix($rows, 'product');
        $trendCreated = $this->buildTrendData($rows, $selectedYear, false);
        $trendClosed = $this->buildTrendData($rows, $selectedYear, true);

        $today = Carbon::today();
        $activeMonthLabel = is_null($selectedYear)
            ? $today->isoFormat('MMMM YYYY')
            : Carbon::create($selectedYear, (int) $today->format('m'), 1)->isoFormat('MMMM YYYY');

        return [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'source' => $source,
            'period' => [
                'month_label' => $activeMonthLabel,
                'week_label' => 'W' . $today->weekOfMonth,
                'overdue_until' => $today->format('j/n/Y'),
            ],
            'filters' => [
                'selected_year' => $selectedYear,
                'selected_bank' => $selectedBank,
                'years' => $years,
                'banks' => $this->availableBanks($rows),
            ],
            'summary_cards' => [
                'total_temuan' => [
                    'title' => 'Total Temuan',
                    'rows' => $statusSummary,
                    'grand_total' => $this->summaryTotal($statusSummary),
                ],
                'resume_temuan' => [
                    'title' => 'Resume Temuan',
                    'rows' => $statusSummary,
                    'grand_total' => $this->summaryTotal($statusSummary),
                ],
                'temuan_jatuh_tempo' => [
                    'title' => 'Temuan Jatuh Tempo',
                    'rows' => $overdueSummary,
                    'grand_total' => $this->summaryTotal($overdueSummary),
                ],
            ],
            'executive_actions' => $this->executiveActions(),
            'rekap' => [
                'rows' => $rekapRows,
                'bar_chart' => $this->buildRekapBarChart($rekapRows),
            ],
            'maps' => [
                'points' => $this->buildMapPoints($rows),
            ],
            'progress' => [
                'by_bank' => $progressByBank,
                'by_region' => $progressByRegion,
                'by_product' => $progressByProduct,
                'status_by_bank_chart' => $this->buildStatusByGroupChart($progressByBank['rows'], 'group'),
                'region_percentage_chart' => $this->buildStatusPercentageChart($progressByRegion['rows']),
            ],
            'trend' => [
                'created' => $trendCreated,
                'closed' => $trendClosed,
            ],
            'detail_outstanding' => [
                'rows' => $this->buildDetailRows($rows),
            ],
            'duedate_maturity' => $this->buildDueMaturityData($rows),
            'status_colors' => $this->statusColors(),
        ];
    }

    /**
     * @return array{year: int|null, bank: string|null}
     */
    private function normalizeFilters(array $filters): array
    {
        $year = null;
        $bank = null;

        if (array_key_exists('year', $filters) && is_numeric($filters['year'])) {
            $candidate = (int) $filters['year'];
            if ($candidate >= 2000 && $candidate <= 2100) {
                $year = $candidate;
            }
        }

        if (array_key_exists('bank', $filters)) {
            $candidate = strtoupper(trim((string) $filters['bank']));
            if ($candidate !== '' && $candidate !== 'ALL') {
                $bank = $candidate;
            }
        }

        return [
            'year' => $year,
            'bank' => $bank,
        ];
    }

    private function normalizeSelectedBank(?string $bank): ?string
    {
        if (is_null($bank)) {
            return null;
        }

        $candidate = strtoupper(trim($bank));

        return ($candidate === '' || $candidate === 'ALL') ? null : $candidate;
    }

    private function loadRisks(?int $year): Collection
    {
        $query = Risk::with(['category', 'creator', 'treatments'])->orderByDesc('created_at');

        if (!is_null($year)) {
            $query->where(function ($nested) use ($year) {
                $nested->where('year', $year)
                    ->orWhereYear('created_at', $year);
            });
        }

        return $query->get();
    }

    private function transformRisk(Risk $risk): array
    {
        $createdAt = $this->safeCarbon(optional($risk)->created_at);
        $updatedAt = $this->safeCarbon(optional($risk)->updated_at);
        $year = (int) ($risk->year ?: optional($createdAt)->format('Y') ?: now()->format('Y'));
        $division = RiskOwnerAccess::displayLabel($risk->risk_owner, 'Unknown');

        $bank = $this->resolveBankLabel([
            (string) $risk->risk_title,
            (string) $risk->risk_description,
            (string) $risk->risk_owner,
            (string) optional($risk->category)->name,
        ]);

        $statusLabel = $this->normalizeStatus((string) $risk->status, (string) $risk->risk_description);
        $statusDetail = $statusLabel === 'Closed' ? 'Closed 100%' : $statusLabel;

        $product = $this->resolveProductLabel((string) $risk->risk_owner, (string) $risk->risk_title, (string) $risk->risk_description);

        $orderedTreatments = collect($risk->treatments ?? [])->sortBy(function ($treatment) {
            $deadline = $this->safeCarbon(optional($treatment)->deadline);

            return is_null($deadline) ? PHP_INT_MAX : $deadline->timestamp;
        })->values();

        $primaryTreatment = $orderedTreatments->first();
        $taskText = trim((string) (optional($primaryTreatment)->description ?: $risk->risk_description ?: $risk->risk_title ?: '-'));
        $assignedTo = trim((string) (optional($primaryTreatment)->pic ?: optional($risk->creator)->name ?: '-'));

        $overdueItems = [];
        foreach ($orderedTreatments as $treatment) {
            $deadline = $this->safeCarbon(optional($treatment)->deadline);
            if (is_null($deadline) || $deadline->greaterThanOrEqualTo(Carbon::today())) {
                continue;
            }

            $months = (int) floor($deadline->diffInDays(Carbon::today()) / 30);
            $overdueItems[] = [
                'bucket' => $this->resolveAgingBucket($months),
                'months_overdue' => $months,
                'deadline' => $deadline->format('Y-m-d'),
            ];
        }

        $nearestDeadline = collect($overdueItems)->pluck('deadline')->filter()->sort()->first();

        return [
            'risk_db_id' => (int) $risk->id,
            'risk_id' => (string) ($risk->risk_id ?: ('RISK-' . $risk->id)),
            'bank' => $bank,
            'region' => $this->resolveRegionFromBank($bank),
            'year' => $year,
            'auditee' => $division,
            'division' => $division,
            'product' => $product,
            'product_name' => trim((string) ($risk->risk_title ?: '-')),
            'status_label' => $statusLabel,
            'status_detail' => $statusDetail,
            'task' => $taskText === '' ? '-' : $taskText,
            'assigned_to' => $assignedTo === '' ? '-' : $assignedTo,
            'pic' => trim((string) (optional($primaryTreatment)->pic ?: '-')) ?: '-',
            'created_at_obj' => $createdAt,
            'created_day' => is_null($createdAt) ? '-' : $createdAt->format('d'),
            'created_month' => is_null($createdAt) ? '-' : $createdAt->isoFormat('MMMM'),
            'created_year' => is_null($createdAt) ? $year : (int) $createdAt->format('Y'),
            'closed_reference_at_obj' => $statusLabel === 'Closed' ? $updatedAt : null,
            'is_overdue' => !empty($overdueItems),
            'overdue_items' => $overdueItems,
            'overdue_until' => $nearestDeadline,
            'total' => 1,
            '_source' => 'database',
        ];
    }

    /**
     * @param  mixed  $row
     * @return array<string, mixed>|null
     */
    private function sanitizePreparedRow($row): ?array
    {
        if (!is_array($row)) {
            return null;
        }

        $division = trim((string) ($row['division'] ?? $row['auditee'] ?? 'Unknown'));
        if ($division === '') {
            $division = 'Unknown';
        }

        $title = trim((string) ($row['product_name'] ?? ''));
        $taskText = trim((string) ($row['task'] ?? ''));
        $ownerSource = trim((string) ($row['auditee'] ?? $row['division'] ?? ''));

        $bankRaw = strtoupper(trim((string) ($row['bank'] ?? '')));
        $bank = $bankRaw === ''
            ? $this->resolveBankLabel([$title, $taskText, $ownerSource])
            : $this->resolveBankLabel([$bankRaw, $title, $taskText, $ownerSource]);

        if ($bankRaw !== '' && $bank === $this->defaultBankName() && !Str::contains($bankRaw, $this->defaultBankName())) {
            // Keep explicit bank from import even if it is not listed in config mapping.
            $bank = $bankRaw;
        }

        $status = $this->normalizeStatus(
            (string) ($row['status_label'] ?? $row['status'] ?? ''),
            (string) ($row['status_detail'] ?? $taskText)
        );

        $createdAt = $this->safeCarbon($row['created_at_obj'] ?? $row['created_at'] ?? null);
        $closedAt = $this->safeCarbon($row['closed_reference_at_obj'] ?? $row['closed_at'] ?? null);
        $year = (int) ($row['year'] ?? (optional($createdAt)->format('Y') ?: now()->format('Y')));

        $productRaw = trim((string) ($row['product'] ?? ''));
        $product = $productRaw === ''
            ? $this->resolveProductLabel($ownerSource, $title, $taskText)
            : $productRaw;

        $statusDetail = trim((string) ($row['status_detail'] ?? ''));
        if ($statusDetail === '') {
            $statusDetail = $status === 'Closed' ? 'Closed 100%' : $status;
        }

        $overdueItems = $this->normalizeOverdueItemsArray($row['overdue_items'] ?? []);
        $nearestDeadline = collect($overdueItems)->pluck('deadline')->filter()->sort()->first();

        return [
            'risk_db_id' => (int) ($row['risk_db_id'] ?? 0),
            'risk_id' => (string) ($row['risk_id'] ?? $row['log_css'] ?? '-'),
            'bank' => $bank,
            'region' => trim((string) ($row['region'] ?? '')) ?: $this->resolveRegionFromBank($bank),
            'year' => $year,
            'auditee' => trim((string) ($row['auditee'] ?? $division)) ?: 'Unknown',
            'division' => $division,
            'product' => $product,
            'product_name' => $title !== '' ? $title : '-',
            'status_label' => $status,
            'status_detail' => $statusDetail,
            'task' => $taskText !== '' ? $taskText : '-',
            'assigned_to' => trim((string) ($row['assigned_to'] ?? '-')) ?: '-',
            'pic' => trim((string) ($row['pic'] ?? '-')) ?: '-',
            'created_at_obj' => $createdAt,
            'created_day' => is_null($createdAt) ? '-' : $createdAt->format('d'),
            'created_month' => is_null($createdAt) ? '-' : $createdAt->isoFormat('MMMM'),
            'created_year' => is_null($createdAt) ? $year : (int) $createdAt->format('Y'),
            'closed_reference_at_obj' => $status === 'Closed' ? $closedAt : null,
            'is_overdue' => !empty($overdueItems),
            'overdue_items' => $overdueItems,
            'overdue_until' => $nearestDeadline,
            'total' => (int) ($row['total'] ?? 1) > 0 ? (int) ($row['total'] ?? 1) : 1,
            '_source' => (string) ($row['_source'] ?? 'normalized'),
        ];
    }

    /**
     * @param  mixed  $items
     * @return array<int, array{bucket: string, months_overdue: int, deadline: string}>
     */
    private function normalizeOverdueItemsArray($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $deadlineRaw = trim((string) ($item['deadline'] ?? ''));
            $months = is_numeric($item['months_overdue'] ?? null) ? (int) $item['months_overdue'] : 0;
            $bucket = trim((string) ($item['bucket'] ?? ''));

            if ($bucket === '' && $months <= 0 && $deadlineRaw === '') {
                continue;
            }

            if ($bucket === '') {
                $bucket = $this->resolveAgingBucket($months);
            }

            if ($deadlineRaw !== '') {
                $deadline = $this->safeCarbon($deadlineRaw);
                if ($deadline instanceof Carbon) {
                    $deadlineRaw = $deadline->format('Y-m-d');
                    if ($months <= 0 && $deadline->lessThan(Carbon::today())) {
                        $months = (int) floor($deadline->diffInDays(Carbon::today()) / 30);
                    }
                }
            }

            $result[] = [
                'bucket' => $bucket,
                'months_overdue' => $months,
                'deadline' => $deadlineRaw,
            ];
        }

        return $result;
    }

    private function buildStatusSummary(Collection $rows): array
    {
        $summary = [];
        foreach ($this->statusOrder() as $status) {
            $summary[] = [
                'label' => $status,
                'count' => (int) $rows->where('status_label', $status)->count(),
            ];
        }

        return $summary;
    }

    private function buildOverdueSummary(Collection $rows): array
    {
        $overdueRows = $rows->filter(function ($row) {
            return (bool) ($row['is_overdue'] ?? false);
        });

        $summary = [];
        foreach ($this->statusOrder() as $status) {
            $summary[] = [
                'label' => $status,
                'count' => (int) $overdueRows->where('status_label', $status)->count(),
            ];
        }

        return $summary;
    }

    private function buildRekapRows(Collection $rows): array
    {
        $grouped = $rows->groupBy(function ($row) {
            return implode('|', [
                (string) ($row['region'] ?? '-'),
                (string) ($row['bank'] ?? '-'),
                (string) ($row['year'] ?? '-'),
                (string) ($row['auditee'] ?? '-'),
            ]);
        });

        $result = [];
        foreach ($grouped as $groupRows) {
            $first = $groupRows->first();
            $result[] = [
                'region' => (string) ($first['region'] ?? '-'),
                'bank' => (string) ($first['bank'] ?? '-'),
                'year' => (string) ($first['year'] ?? '-'),
                'auditee' => (string) ($first['auditee'] ?? '-'),
                'total' => (int) $groupRows->count(),
                'closed' => (int) $groupRows->where('status_label', 'Closed')->count(),
                'on_progress' => (int) $groupRows->where('status_label', 'On Progress')->count(),
                'domain_bank' => (int) $groupRows->where('status_label', 'Domain Bank')->count(),
            ];
        }

        usort($result, function ($a, $b) {
            return strcmp(
                strtoupper((string) ($a['region'] . '|' . $a['bank'] . '|' . $a['auditee'])),
                strtoupper((string) ($b['region'] . '|' . $b['bank'] . '|' . $b['auditee']))
            );
        });

        return $result;
    }

    private function buildRekapBarChart(array $rekapRows): array
    {
        return [
            'labels' => array_map(function ($row) {
                return (string) ($row['bank'] . ' - ' . (string) ($row['auditee'] ?? '-'));
            }, $rekapRows),
            'datasets' => [
                [
                    'label' => 'Total',
                    'backgroundColor' => '#3b82f6',
                    'data' => array_map(fn ($row) => (int) ($row['total'] ?? 0), $rekapRows),
                ],
                [
                    'label' => 'Closed',
                    'backgroundColor' => $this->statusColors()['Closed'] ?? '#f59e0b',
                    'data' => array_map(fn ($row) => (int) ($row['closed'] ?? 0), $rekapRows),
                ],
                [
                    'label' => 'On Progress',
                    'backgroundColor' => $this->statusColors()['On Progress'] ?? '#f9a8d4',
                    'data' => array_map(fn ($row) => (int) ($row['on_progress'] ?? 0), $rekapRows),
                ],
                [
                    'label' => 'Domain Bank',
                    'backgroundColor' => $this->statusColors()['Domain Bank'] ?? '#ef4444',
                    'data' => array_map(fn ($row) => (int) ($row['domain_bank'] ?? 0), $rekapRows),
                ],
            ],
        ];
    }

    private function buildMapPoints(Collection $rows): array
    {
        $points = [];
        foreach ($rows->groupBy('bank') as $bank => $bankRows) {
            $coords = $this->coordinatesByBank((string) $bank);
            $points[] = [
                'bank' => (string) $bank,
                'lat' => (float) $coords['lat'],
                'lng' => (float) $coords['lng'],
                'total' => (int) $bankRows->count(),
                'closed' => (int) $bankRows->where('status_label', 'Closed')->count(),
                'domain_bank' => (int) $bankRows->where('status_label', 'Domain Bank')->count(),
                'on_progress' => (int) $bankRows->where('status_label', 'On Progress')->count(),
            ];
        }

        usort($points, fn ($a, $b) => strcmp($a['bank'], $b['bank']));

        return $points;
    }

    private function buildPercentageMatrix(Collection $rows, string $groupKey): array
    {
        $groups = $rows->groupBy($groupKey)->map(function (Collection $groupRows, $group) {
            $total = (int) $groupRows->count();
            $closed = (int) $groupRows->where('status_label', 'Closed')->count();
            $domain = (int) $groupRows->where('status_label', 'Domain Bank')->count();
            $progress = (int) $groupRows->where('status_label', 'On Progress')->count();

            return [
                'group' => (string) $group,
                'total' => $total,
                'counts' => [
                    'Closed' => $closed,
                    'Domain Bank' => $domain,
                    'On Progress' => $progress,
                ],
                'percentages' => [
                    'Closed' => $total > 0 ? round(($closed / $total) * 100, 2) : 0.0,
                    'Domain Bank' => $total > 0 ? round(($domain / $total) * 100, 2) : 0.0,
                    'On Progress' => $total > 0 ? round(($progress / $total) * 100, 2) : 0.0,
                ],
            ];
        })->values()->all();

        usort($groups, function ($a, $b) {
            if ((int) $a['total'] !== (int) $b['total']) {
                return ((int) $b['total']) <=> ((int) $a['total']);
            }

            return strcmp(strtoupper((string) $a['group']), strtoupper((string) $b['group']));
        });

        return ['rows' => $groups];
    }

    private function buildStatusByGroupChart(array $rows, string $labelKey): array
    {
        $statusOrder = $this->statusOrder();
        $colors = $this->statusColors();

        return [
            'labels' => array_map(fn ($row) => (string) ($row[$labelKey] ?? '-'), $rows),
            'datasets' => array_map(function ($status) use ($rows, $colors) {
                return [
                    'label' => $status,
                    'backgroundColor' => $colors[$status] ?? '#94a3b8',
                    'data' => array_map(fn ($row) => (int) ($row['counts'][$status] ?? 0), $rows),
                ];
            }, $statusOrder),
        ];
    }

    private function buildStatusPercentageChart(array $rows): array
    {
        $statusOrder = $this->statusOrder();
        $colors = $this->statusColors();

        return [
            'labels' => array_map(fn ($row) => (string) ($row['group'] ?? '-'), $rows),
            'datasets' => array_map(function ($status) use ($rows, $colors) {
                return [
                    'label' => $status,
                    'backgroundColor' => $colors[$status] ?? '#94a3b8',
                    'data' => array_map(fn ($row) => (float) ($row['percentages'][$status] ?? 0), $rows),
                ];
            }, $statusOrder),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $detailRows
     */
    private function buildSnapshotTrendData(array $detailRows, bool $closedDate): array
    {
        $statuses = $closedDate ? ['Closed'] : $this->statusOrder();
        $colors = $this->statusColors();

        $timelineRows = collect($detailRows)->map(function ($row) {
            $status = (string) ($row['status_summary'] ?? '-');
            $day = is_numeric($row['date'] ?? null) ? (int) $row['date'] : 0;
            $month = trim((string) ($row['month'] ?? ''));
            $year = is_numeric($row['year'] ?? null) ? (int) $row['year'] : 0;

            if ($day <= 0 || $year < 2000 || $month === '' || $month === '-' || strtoupper($month) === 'NULL') {
                return null;
            }

            try {
                $date = Carbon::createFromFormat('!j F Y', $day . ' ' . $month . ' ' . $year);
            } catch (\Throwable $exception) {
                return null;
            }

            if (!$date instanceof Carbon) {
                return null;
            }

            return [
                'status' => $status,
                'period' => $date->format('Y-m'),
                'date' => $date,
            ];
        })->filter()->values();

        if ($timelineRows->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => array_map(function ($status) use ($colors) {
                    return [
                        'label' => $status,
                        'borderColor' => $colors[$status] ?? '#2563eb',
                        'backgroundColor' => $colors[$status] ?? '#2563eb',
                        'data' => [],
                    ];
                }, $statuses),
            ];
        }

        $maxTimestamp = (int) $timelineRows->max(function ($row) {
            $date = $row['date'] ?? null;

            return $date instanceof Carbon ? $date->timestamp : 0;
        });

        $endMonth = Carbon::createFromTimestamp($maxTimestamp)->startOfMonth();
        $periods = [];
        $cursor = (clone $endMonth)->subMonths(11);
        for ($i = 0; $i < 12; $i++) {
            $period = (clone $cursor)->addMonths($i);
            $periods[$period->format('Y-m')] = $period;
        }

        $labels = array_map(
            fn (Carbon $period) => $period->isoFormat('MMM YYYY'),
            array_values($periods)
        );

        $datasets = [];
        foreach ($statuses as $status) {
            $datasets[] = [
                'label' => $status,
                'borderColor' => $colors[$status] ?? '#2563eb',
                'backgroundColor' => $colors[$status] ?? '#2563eb',
                'data' => array_map(function (string $periodKey) use ($timelineRows, $status, $closedDate) {
                    return (int) $timelineRows->filter(function ($row) use ($periodKey, $status, $closedDate) {
                        if (($row['period'] ?? null) !== $periodKey) {
                            return false;
                        }

                        if ($closedDate) {
                            return ($row['status'] ?? null) === 'Closed';
                        }

                        return ($row['status'] ?? null) === $status;
                    })->count();
                }, array_keys($periods)),
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    private function buildTrendData(Collection $rows, ?int $selectedYear, bool $closedDate): array
    {
        $dateAccessor = $closedDate ? 'closed_reference_at_obj' : 'created_at_obj';
        $baseRows = $rows->filter(fn ($row) => !is_null($row[$dateAccessor] ?? null));

        $periods = [];
        if (!is_null($selectedYear)) {
            for ($month = 1; $month <= 12; $month++) {
                $start = Carbon::create($selectedYear, $month, 1)->startOfMonth();
                $periods[$start->format('Y-m')] = $start;
            }
        } else {
            $cursor = Carbon::today()->startOfMonth()->subMonths(11);
            for ($i = 0; $i < 12; $i++) {
                $start = (clone $cursor)->addMonths($i);
                $periods[$start->format('Y-m')] = $start;
            }
        }

        $statuses = $closedDate ? ['Closed'] : $this->statusOrder();
        $colors = $this->statusColors();
        $labels = array_map(fn (Carbon $period) => $period->isoFormat('MMM YYYY'), array_values($periods));

        $datasets = [];
        foreach ($statuses as $status) {
            $data = [];
            foreach (array_keys($periods) as $periodKey) {
                $count = $baseRows->filter(function ($row) use ($periodKey, $status, $closedDate, $dateAccessor) {
                    $date = $row[$dateAccessor] ?? null;
                    if (!$date instanceof Carbon) {
                        return false;
                    }

                    if ($closedDate && ($row['status_label'] ?? null) !== 'Closed') {
                        return false;
                    }

                    if (!$closedDate && ($row['status_label'] ?? null) !== $status) {
                        return false;
                    }

                    return $date->format('Y-m') === $periodKey;
                })->count();

                $data[] = (int) $count;
            }

            $datasets[] = [
                'label' => $status,
                'borderColor' => $colors[$status] ?? '#2563eb',
                'backgroundColor' => $colors[$status] ?? '#2563eb',
                'data' => $data,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    private function buildDetailRows(Collection $rows): array
    {
        $result = $rows->map(function ($row) {
            return [
                'bank' => (string) ($row['bank'] ?? '-'),
                'date' => (string) ($row['created_day'] ?? '-'),
                'month' => (string) ($row['created_month'] ?? '-'),
                'year' => (string) ($row['created_year'] ?? '-'),
                'product' => (string) ($row['product'] ?? '-'),
                'product_name' => (string) ($row['product_name'] ?? '-'),
                'status_detail' => (string) ($row['status_detail'] ?? '-'),
                'pic' => (string) ($row['pic'] ?? '-'),
                'log_css' => (string) ($row['risk_id'] ?? '-'),
                'status_summary' => (string) ($row['status_label'] ?? '-'),
                'task' => (string) ($row['task'] ?? '-'),
                'assigned_to' => (string) ($row['assigned_to'] ?? '-'),
                'is_overdue' => (bool) ($row['is_overdue'] ?? false),
            ];
        })->values()->all();

        usort($result, function ($a, $b) {
            return strcmp(
                strtoupper((string) ($a['bank'] . '|' . $a['year'] . '|' . $a['month'])),
                strtoupper((string) ($b['bank'] . '|' . $b['year'] . '|' . $b['month']))
            );
        });

        return $result;
    }

    private function buildDueMaturityData(Collection $rows): array
    {
        $overdueItems = $rows->flatMap(function ($row) {
            $items = [];
            foreach (($row['overdue_items'] ?? []) as $item) {
                $items[] = [
                    'bank' => (string) ($row['bank'] ?? '-'),
                    'division' => (string) ($row['division'] ?? '-'),
                    'product' => (string) ($row['product'] ?? '-'),
                    'bucket' => (string) ($item['bucket'] ?? ''),
                ];
            }

            return $items;
        })->values();

        $byBank = $this->buildAgingTable($overdueItems, 'bank');
        $byDivision = $this->buildAgingTable($overdueItems, 'division');
        $byProduct = $this->buildAgingTable($overdueItems, 'product');

        return [
            'by_bank' => $byBank,
            'by_division' => $byDivision,
            'by_product' => $byProduct,
            'pie_by_bank' => $this->buildAgingPieSlices($byBank),
            'pie_by_division' => $this->buildAgingPieSlices($byDivision),
            'pie_by_product' => $this->buildAgingPieSlices($byProduct),
            'grand_total' => (int) $overdueItems->count(),
        ];
    }

    private function buildAgingTable(Collection $overdueItems, string $groupKey): array
    {
        $grouped = $overdueItems->groupBy($groupKey)->map(function (Collection $items, $group) {
            $bucketLE6 = (int) $items->where('bucket', '<=6 Bulan')->count();
            $bucketLE9 = (int) $items->where('bucket', '<=9 Bulan')->count();
            $bucketGT9 = (int) $items->where('bucket', '>9 Bulan')->count();

            return [
                'group' => (string) $group,
                '<=6 Bulan' => $bucketLE6,
                '<=9 Bulan' => $bucketLE9,
                '>9 Bulan' => $bucketGT9,
                'grand_total' => $bucketLE6 + $bucketLE9 + $bucketGT9,
            ];
        })->filter(fn ($row) => (int) ($row['grand_total'] ?? 0) > 0)->values()->all();

        usort($grouped, function ($a, $b) {
            if ((int) $a['grand_total'] !== (int) $b['grand_total']) {
                return ((int) $b['grand_total']) <=> ((int) $a['grand_total']);
            }

            return strcmp(strtoupper((string) $a['group']), strtoupper((string) $b['group']));
        });

        $totalLE6 = array_sum(array_map(fn ($row) => (int) ($row['<=6 Bulan'] ?? 0), $grouped));
        $totalLE9 = array_sum(array_map(fn ($row) => (int) ($row['<=9 Bulan'] ?? 0), $grouped));
        $totalGT9 = array_sum(array_map(fn ($row) => (int) ($row['>9 Bulan'] ?? 0), $grouped));

        $grouped[] = [
            'group' => 'Grand Total',
            '<=6 Bulan' => $totalLE6,
            '<=9 Bulan' => $totalLE9,
            '>9 Bulan' => $totalGT9,
            'grand_total' => $totalLE6 + $totalLE9 + $totalGT9,
            'is_total' => true,
        ];

        return $grouped;
    }

    private function buildAgingPieSlices(array $tableRows): array
    {
        $slices = [];
        foreach ($tableRows as $row) {
            if (!empty($row['is_total'])) {
                continue;
            }

            foreach (['<=6 Bulan', '<=9 Bulan', '>9 Bulan'] as $bucket) {
                $count = (int) ($row[$bucket] ?? 0);
                if ($count <= 0) {
                    continue;
                }

                $slices[] = [
                    'label' => (string) ($row['group'] . ' ' . $bucket),
                    'count' => $count,
                ];
            }
        }

        usort($slices, fn ($a, $b) => ((int) $b['count']) <=> ((int) $a['count']));

        return $slices;
    }

    private function resolveAgingBucket(int $monthsOverdue): string
    {
        if ($monthsOverdue <= 6) {
            return '<=6 Bulan';
        }

        if ($monthsOverdue <= 9) {
            return '<=9 Bulan';
        }

        return '>9 Bulan';
    }

    private function normalizeStatus(string $status, string $description = ''): string
    {
        $raw = strtolower(trim($status));
        $descriptionRaw = strtolower($description);

        foreach ((array) config('audit_dashboard.status.domain_keywords', []) as $keyword) {
            $needle = strtolower(trim((string) $keyword));
            if ($needle !== '' && (Str::contains($raw, $needle) || Str::contains($descriptionRaw, $needle))) {
                return 'Domain Bank';
            }
        }

        foreach ((array) config('audit_dashboard.status.closed_keywords', []) as $keyword) {
            $needle = strtolower(trim((string) $keyword));
            if ($needle !== '' && (Str::contains($raw, $needle) || Str::contains($descriptionRaw, $needle))) {
                return 'Closed';
            }
        }

        $default = trim((string) config('audit_dashboard.status.default', 'On Progress'));

        return $default !== '' ? $default : 'On Progress';
    }

    private function resolveBankLabel(array $parts): string
    {
        $haystack = strtoupper(implode(' ', array_filter($parts, fn ($part) => trim((string) $part) !== '')));

        foreach ($this->bankMappings() as $mapping) {
            foreach ($mapping['keywords'] as $keyword) {
                if ($keyword !== '' && Str::contains($haystack, strtoupper($keyword))) {
                    return $mapping['name'];
                }
            }
        }

        return $this->defaultBankName();
    }

    private function resolveRegionFromBank(string $bank): string
    {
        foreach ($this->bankMappings() as $mapping) {
            if ($mapping['name'] === strtoupper(trim($bank))) {
                return $mapping['region'];
            }
        }

        return 'Others';
    }

    private function coordinatesByBank(string $bank): array
    {
        foreach ($this->bankMappings() as $mapping) {
            if ($mapping['name'] === strtoupper(trim($bank))) {
                return $mapping['coordinates'];
            }
        }

        $fallback = (array) config('audit_dashboard.bank.fallback_coordinates', ['lat' => -6.2088, 'lng' => 106.8456]);

        return [
            'lat' => (float) ($fallback['lat'] ?? -6.2088),
            'lng' => (float) ($fallback['lng'] ?? 106.8456),
        ];
    }

    private function resolveProductLabel(string $riskOwner, string $title, string $description): string
    {
        $owner = strtoupper(trim(RiskOwnerAccess::displayLabel($riskOwner, '')));

        foreach ($this->productMappings() as $mapping) {
            foreach ($mapping['keywords'] as $keyword) {
                if ($keyword !== '' && Str::contains($owner, strtoupper($keyword))) {
                    return $mapping['label'];
                }
            }
        }

        $source = $owner;
        if ($source === '') {
            $source = strtoupper(trim($title));
        }
        if ($source === '') {
            $source = strtoupper(trim($description));
        }
        if ($source === '') {
            return $this->defaultProductLabel();
        }

        $tokenLimit = max(1, (int) config('audit_dashboard.product.token_fallback_words', 2));
        $maxLength = max(4, (int) config('audit_dashboard.product.max_length', 24));
        $tokens = array_values(array_filter(preg_split('/\s+/', $source) ?: []));

        if (empty($tokens)) {
            return $this->defaultProductLabel();
        }

        return Str::limit(implode(' ', array_slice($tokens, 0, $tokenLimit)), $maxLength, '');
    }

    private function safeCarbon($value): ?Carbon
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        try {
            return $value instanceof Carbon ? $value : Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function summaryTotal(array $rows): int
    {
        return (int) array_sum(array_map(fn ($row) => (int) ($row['count'] ?? 0), $rows));
    }

    /** @return array<int, int> */
    private function availableYearsFromDatabase(): array
    {
        $years = Risk::query()
            ->select('year')
            ->whereNotNull('year')
            ->distinct()
            ->pluck('year')
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();

        rsort($years, SORT_NUMERIC);

        return empty($years) ? [(int) now()->format('Y')] : $years;
    }

    /** @return array<int, int> */
    private function availableYearsFromRows(Collection $rows): array
    {
        $years = $rows->pluck('year')
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return empty($years) ? [(int) now()->format('Y')] : $years;
    }

    private function availableBanks(Collection $rows): array
    {
        $banksFromConfig = array_map(fn ($mapping) => (string) $mapping['name'], $this->bankMappings());
        $dynamic = $rows->pluck('bank')
            ->map(fn ($value) => strtoupper(trim((string) $value)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $all = array_values(array_unique(array_merge($banksFromConfig, $dynamic)));
        sort($all, SORT_NATURAL | SORT_FLAG_CASE);

        return $all;
    }

    /** @return array<int, string> */
    private function statusOrder(): array
    {
        $order = array_values(array_filter(array_map(function ($value) {
            return trim((string) $value);
        }, (array) config('audit_dashboard.status.order', ['Closed', 'Domain Bank', 'On Progress']))));

        return empty($order) ? ['Closed', 'Domain Bank', 'On Progress'] : $order;
    }

    /** @return array<string, string> */
    private function statusColors(): array
    {
        return array_map(fn ($value) => (string) $value, (array) config('audit_dashboard.status.colors', []));
    }

    /** @return array<int, string> */
    private function executiveActions(): array
    {
        $actions = array_values(array_filter(array_map(function ($value) {
            return trim((string) $value);
        }, (array) config('audit_dashboard.executive_actions', []))));

        return empty($actions)
            ? [
                'Reminder (email, WA) kepada PIC penyelesaian temuan.',
                'Koordinasi meeting internal dengan divisi terkait.',
                'Koordinasi berkala dengan klien untuk memastikan komitmen close temuan.',
            ]
            : $actions;
    }

    private function defaultBankName(): string
    {
        $name = trim((string) config('audit_dashboard.bank.default', 'INTERNAL'));

        return $name !== '' ? strtoupper($name) : 'INTERNAL';
    }

    private function defaultProductLabel(): string
    {
        $name = trim((string) config('audit_dashboard.product.default', 'UNKNOWN'));

        return $name !== '' ? $name : 'UNKNOWN';
    }

    /**
     * @return array<int, array{name: string, keywords: array<int, string>, coordinates: array{lat: float, lng: float}, region: string}>
     */
    private function bankMappings(): array
    {
        $rows = (array) config('audit_dashboard.bank.mappings', []);
        $mappings = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = strtoupper(trim((string) ($row['name'] ?? '')));
            if ($name === '') {
                continue;
            }

            $keywords = array_values(array_filter(array_map(function ($keyword) {
                return strtoupper(trim((string) $keyword));
            }, (array) ($row['keywords'] ?? []))));

            $coord = (array) ($row['coordinates'] ?? []);
            $mappings[] = [
                'name' => $name,
                'keywords' => $keywords,
                'coordinates' => [
                    'lat' => (float) ($coord['lat'] ?? 0),
                    'lng' => (float) ($coord['lng'] ?? 0),
                ],
                'region' => trim((string) ($row['region'] ?? 'Others')) ?: 'Others',
            ];
        }

        if (empty($mappings)) {
            $mappings[] = [
                'name' => 'INTERNAL',
                'keywords' => ['INTERNAL'],
                'coordinates' => ['lat' => -6.2088, 'lng' => 106.8456],
                'region' => 'Internal',
            ];
        }

        return $mappings;
    }

    /** @return array<int, array{label: string, keywords: array<int, string>}> */
    private function productMappings(): array
    {
        $rows = (array) config('audit_dashboard.product.mappings', []);
        $mappings = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $keywords = array_values(array_filter(array_map(function ($keyword) {
                return strtoupper(trim((string) $keyword));
            }, (array) ($row['keywords'] ?? []))));

            if (empty($keywords)) {
                continue;
            }

            $mappings[] = [
                'label' => $label,
                'keywords' => $keywords,
            ];
        }

        return $mappings;
    }
}


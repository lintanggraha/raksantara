<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class AuditDashboardImportAdapter
{
    /**
     * Normalize parser output rows into audit dashboard row schema.
     *
     * This adapter is intentionally format-agnostic so later import parser
     * (xlsx/docx/csv) only needs to map source columns into plain arrays.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $createdAt = $this->resolveDate($row, ['created_at', 'created_date', 'created']);
            $closedAt = $this->resolveDate($row, ['closed_at', 'closed_date', 'date_closed']);
            $year = $this->resolveYear($row, $createdAt);

            $status = $this->firstString($row, ['status_label', 'status', 'status_exit']) ?: 'On Progress';
            $statusDetail = $this->firstString($row, ['status_detail', 'status_summary']) ?: $status;

            $overdueItems = $this->normalizeOverdueItems($row);

            $normalized[] = [
                'risk_db_id' => (int) ($row['risk_db_id'] ?? $index + 1),
                'risk_id' => $this->firstString($row, ['risk_id', 'log_css', 'log_id']) ?: ('IMPORT-' . ($index + 1)),
                'bank' => strtoupper($this->firstString($row, ['bank', 'bank_name']) ?: ''),
                'region' => $this->firstString($row, ['region']) ?: '',
                'year' => $year,
                'auditee' => $this->firstString($row, ['auditee', 'division', 'divisi']) ?: 'Unknown',
                'division' => $this->firstString($row, ['division', 'divisi', 'auditee']) ?: 'Unknown',
                'product' => $this->firstString($row, ['product']) ?: '',
                'product_name' => $this->firstString($row, ['product_name', 'risk_title', 'title']) ?: '-',
                'status_label' => $status,
                'status_detail' => $statusDetail,
                'task' => $this->firstString($row, ['task', 'description', 'risk_description']) ?: '-',
                'assigned_to' => $this->firstString($row, ['assigned_to', 'assignee', 'pic']) ?: '-',
                'pic' => $this->firstString($row, ['pic', 'person_in_charge', 'assigned_to']) ?: '-',
                'created_at_obj' => $createdAt,
                'created_day' => is_null($createdAt) ? '-' : $createdAt->format('d'),
                'created_month' => is_null($createdAt) ? '-' : $createdAt->isoFormat('MMMM'),
                'created_year' => is_null($createdAt) ? $year : (int) $createdAt->format('Y'),
                'closed_reference_at_obj' => $closedAt,
                'is_overdue' => !empty($overdueItems),
                'overdue_items' => $overdueItems,
                'overdue_until' => $this->resolveNearestOverdueDate($overdueItems),
                'total' => 1,
                '_source' => 'import',
            ];
        }

        return $normalized;
    }

    private function resolveDate(array $row, array $keys): ?Carbon
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }

            $candidate = $row[$key];
            if ($candidate instanceof Carbon) {
                return $candidate;
            }

            if (is_string($candidate) && trim($candidate) !== '') {
                try {
                    return Carbon::parse($candidate);
                } catch (\Throwable $e) {
                    // Try next key.
                }
            }
        }

        return null;
    }

    private function resolveYear(array $row, ?Carbon $fallbackDate): int
    {
        $yearCandidate = $row['year'] ?? null;
        if (is_numeric($yearCandidate)) {
            $year = (int) $yearCandidate;
            if ($year >= 2000 && $year <= 2100) {
                return $year;
            }
        }

        if ($fallbackDate instanceof Carbon) {
            return (int) $fallbackDate->format('Y');
        }

        return (int) now()->format('Y');
    }

    /**
     * @return array<int, array{bucket: string, months_overdue: int, deadline: string}>
     */
    private function normalizeOverdueItems(array $row): array
    {
        $items = [];

        if (isset($row['overdue_items']) && is_array($row['overdue_items'])) {
            foreach ($row['overdue_items'] as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $bucket = trim((string) ($item['bucket'] ?? ''));
                $months = (int) ($item['months_overdue'] ?? 0);
                $deadline = trim((string) ($item['deadline'] ?? ''));

                if ($bucket === '' && $months <= 0 && $deadline === '') {
                    continue;
                }

                if ($bucket === '') {
                    $bucket = $this->bucketFromMonths($months);
                }

                $items[] = [
                    'bucket' => $bucket,
                    'months_overdue' => $months,
                    'deadline' => $deadline,
                ];
            }
        }

        if (!empty($items)) {
            return $items;
        }

        $deadlineRaw = $this->firstString($row, ['deadline', 'due_date', 'target_close_date']);
        if ($deadlineRaw === null) {
            return [];
        }

        try {
            $deadline = Carbon::parse($deadlineRaw);
        } catch (\Throwable $e) {
            return [];
        }

        if ($deadline->greaterThanOrEqualTo(Carbon::today())) {
            return [];
        }

        $monthsOverdue = (int) floor($deadline->diffInDays(Carbon::today()) / 30);

        return [[
            'bucket' => $this->bucketFromMonths($monthsOverdue),
            'months_overdue' => $monthsOverdue,
            'deadline' => $deadline->format('Y-m-d'),
        ]];
    }

    private function resolveNearestOverdueDate(array $items): ?string
    {
        $deadlines = array_values(array_filter(array_map(function ($item) {
            return is_array($item) ? trim((string) ($item['deadline'] ?? '')) : '';
        }, $items)));

        if (empty($deadlines)) {
            return null;
        }

        sort($deadlines, SORT_STRING);

        return (string) $deadlines[0];
    }

    private function bucketFromMonths(int $months): string
    {
        if ($months <= 6) {
            return '<=6 Bulan';
        }

        if ($months <= 9) {
            return '<=9 Bulan';
        }

        return '>9 Bulan';
    }

    private function firstString(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }

            $value = trim((string) $row[$key]);
            if ($value === '') {
                continue;
            }

            return Str::replace(["\r", "\n"], ' ', $value);
        }

        return null;
    }
}

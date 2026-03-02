<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditDashboardSpreadsheetImportService
{
    /** @var array<int, string> */
    private const PREFERRED_SHEETS = [
        'SUMMARY UPDATE',
        'UPDATE JULI',
    ];

    /**
     * @return array{
     *     rows: array<int, array<string, mixed>>,
     *     meta: array<string, mixed>
     * }
     */
    public function parse(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new \RuntimeException('File import audit tidak ditemukan.');
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (\Throwable $e) {
            throw new \RuntimeException('File Excel tidak dapat dibaca. Pastikan format file valid.', 0, $e);
        }

        $sheet = $this->resolveWorksheet($spreadsheet);
        [$headerRow, $columnMap] = $this->resolveHeaderMap($sheet);

        $highestRow = (int) $sheet->getHighestDataRow();
        $highestColumn = (string) $sheet->getHighestDataColumn();
        $rows = [];

        for ($rowIndex = $headerRow + 1; $rowIndex <= $highestRow; $rowIndex++) {
            // Use calculated + formatted values so formula-based columns (e.g. aging) are imported as displayed.
            $cells = $sheet->rangeToArray('A' . $rowIndex . ':' . $highestColumn . $rowIndex, null, true, true, false)[0];
            $mapped = $this->extractMappedRow($cells, $columnMap);
            $prepared = $this->buildPreparedRow($mapped, $rowIndex);

            if (!is_null($prepared)) {
                $rows[] = $prepared;
            }
        }

        if (empty($rows)) {
            throw new \RuntimeException('Tidak ada baris data valid yang dapat diimport dari sheet terpilih.');
        }

        return [
            'rows' => $rows,
            'meta' => $this->buildMeta($rows, $sheet->getTitle(), $headerRow),
        ];
    }

    private function resolveWorksheet(Spreadsheet $spreadsheet): Worksheet
    {
        $worksheets = $spreadsheet->getAllSheets();

        foreach (self::PREFERRED_SHEETS as $preferredName) {
            foreach ($worksheets as $worksheet) {
                if (strcasecmp(trim($worksheet->getTitle()), $preferredName) === 0) {
                    return $worksheet;
                }
            }
        }

        $bestWorksheet = null;
        $bestScore = -1;

        foreach ($worksheets as $worksheet) {
            try {
                [, $columnMap] = $this->resolveHeaderMap($worksheet);
            } catch (\Throwable $e) {
                continue;
            }

            $score = $this->headerMapScore($columnMap);
            if ($score > $bestScore) {
                $bestWorksheet = $worksheet;
                $bestScore = $score;
            }
        }

        if ($bestWorksheet instanceof Worksheet) {
            return $bestWorksheet;
        }

        throw new \RuntimeException('Sheet import audit tidak ditemukan. Pastikan file memuat sheet detail temuan audit.');
    }

    /**
     * @return array{0: int, 1: array<string, int>}
     */
    private function resolveHeaderMap(Worksheet $sheet): array
    {
        $highestRow = min(40, (int) $sheet->getHighestDataRow());
        $highestColumn = (string) $sheet->getHighestDataColumn();

        $bestRow = 0;
        $bestMap = [];
        $bestScore = -1;

        for ($rowIndex = 1; $rowIndex <= $highestRow; $rowIndex++) {
            $cells = $sheet->rangeToArray('A' . $rowIndex . ':' . $highestColumn . $rowIndex, null, false, false, false)[0];
            $columnMap = [];
            $maturityColumns = [];

            foreach ($cells as $columnIndex => $rawHeader) {
                $normalizedHeader = $this->normalizeHeader($rawHeader);
                if ($normalizedHeader === '') {
                    continue;
                }

                $key = $this->detectHeaderKey($normalizedHeader);
                if (is_null($key)) {
                    continue;
                }

                if ($key === 'maturity') {
                    $maturityColumns[] = $columnIndex;
                    continue;
                }

                if (!array_key_exists($key, $columnMap)) {
                    $columnMap[$key] = $columnIndex;
                }
            }

            if (!empty($maturityColumns)) {
                $columnMap['maturity_text'] = $maturityColumns[0];
                if (array_key_exists(1, $maturityColumns)) {
                    $columnMap['maturity_days'] = $maturityColumns[1];
                }
            }

            $score = $this->headerMapScore($columnMap);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMap = $columnMap;
                $bestRow = $rowIndex;
            }
        }

        if ($bestScore < 8) {
            throw new \RuntimeException('Header import tidak dikenali pada sheet "' . $sheet->getTitle() . '".');
        }

        return [$bestRow, $bestMap];
    }

    /**
     * @param  array<string, int>  $columnMap
     */
    private function headerMapScore(array $columnMap): int
    {
        $required = ['bank', 'year'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $columnMap)) {
                return -1;
            }
        }

        $hasStatus = array_key_exists('status_exit', $columnMap) || array_key_exists('status', $columnMap) || array_key_exists('status_detail', $columnMap);
        if (!$hasStatus) {
            return -1;
        }

        $score = count($columnMap);
        $score += 3;

        if (array_key_exists('task', $columnMap) || array_key_exists('subject_audit', $columnMap)) {
            $score += 2;
        }
        if (array_key_exists('product', $columnMap) || array_key_exists('product_name', $columnMap)) {
            $score += 1;
        }
        if (array_key_exists('log_css', $columnMap)) {
            $score += 1;
        }

        return $score;
    }

    private function detectHeaderKey(string $header): ?string
    {
        if ($this->containsAny($header, ['status detail'])) {
            return 'status_detail';
        }

        if ($this->containsAny($header, ['status exit'])) {
            return 'status_exit';
        }

        if ($this->containsAny($header, ['update commitment date'])) {
            return 'update_commitment_date';
        }

        if ($this->containsAny($header, ['orginal commitment date', 'original commitment date'])) {
            return 'original_commitment_date';
        }

        if ($this->containsAny($header, ['ref no temuan'])) {
            return 'ref_no';
        }

        if ($this->containsAny($header, ['log css'])) {
            return 'log_css';
        }

        if ($this->containsAny($header, ['subject audit', 'subject'])) {
            return 'subject_audit';
        }

        if ($this->containsAny($header, ['audit years', 'years', 'year'])) {
            return 'year';
        }

        if ($this->containsAny($header, ['pic penyelesaian', 'pic'])) {
            return 'pic';
        }

        if ($this->containsAny($header, ['product name'])) {
            return 'product_name';
        }

        if ($this->containsAny($header, ['product'])) {
            return 'product';
        }

        if ($this->containsAny($header, ['division', 'auditee unit kerja struktur baru'])) {
            return 'division';
        }

        if ($this->containsAny($header, ['deskripsi', 'task'])) {
            return 'task';
        }

        if ($this->containsAny($header, ['aging'])) {
            return 'aging';
        }

        if ($this->containsAny($header, ['today'])) {
            return 'today';
        }

        if ($this->containsAny($header, ['bank'])) {
            return 'bank';
        }

        if ($this->containsAny($header, ['maturity'])) {
            return 'maturity';
        }

        if ($this->containsAny($header, ['status'])) {
            return 'status';
        }

        if ($this->containsAny($header, ['nomor'])) {
            return 'nomor';
        }

        return null;
    }

    private function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && Str::contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  mixed  $value
     */
    private function normalizeHeader($value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        $text = str_replace(["\r", "\n", "\t"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = strtolower(trim((string) $text));

        return $text;
    }

    /**
     * @param  array<int, mixed>  $cells
     * @param  array<string, int>  $columnMap
     * @return array<string, mixed>
     */
    private function extractMappedRow(array $cells, array $columnMap): array
    {
        $row = [];
        foreach ($columnMap as $key => $columnIndex) {
            $row[$key] = $cells[$columnIndex] ?? null;
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $mapped
     * @return array<string, mixed>|null
     */
    private function buildPreparedRow(array $mapped, int $rowIndex): ?array
    {
        $bank = strtoupper((string) ($this->nullIfEmpty($mapped['bank'] ?? null) ?? ''));
        $task = $this->nullIfEmpty($mapped['task'] ?? null);
        $subjectAudit = $this->nullIfEmpty($mapped['subject_audit'] ?? null);

        if ($bank === '' && is_null($task) && is_null($subjectAudit)) {
            return null;
        }

        if ($bank !== '' && Str::contains($bank, 'TOTAL')) {
            return null;
        }

        if ($bank === '') {
            $bank = 'COLLEGA';
        }

        $statusRaw = $this->firstNonNull([
            $this->nullIfEmpty($mapped['status_exit'] ?? null),
            $this->nullIfEmpty($mapped['status'] ?? null),
            $this->nullIfEmpty($mapped['status_detail'] ?? null),
        ]) ?: 'On Progress';
        $statusLabel = $this->normalizeStatusLabel($statusRaw);
        $statusDetail = $this->nullIfEmpty($mapped['status_detail'] ?? null) ?: ($statusLabel === 'Closed' ? 'Closed 100%' : $statusLabel);

        $division = $this->firstNonNull([
            $this->nullIfEmpty($mapped['division'] ?? null),
            $subjectAudit,
        ]) ?: 'Unknown';

        $auditee = $subjectAudit ?: $division;
        $product = $this->firstNonNull([
            $this->nullIfEmpty($mapped['product'] ?? null),
            $division,
        ]) ?: 'UNKNOWN';
        $productName = $this->firstNonNull([
            $this->nullIfEmpty($mapped['product_name'] ?? null),
            $subjectAudit,
            $product,
        ]) ?: 'Unknown';

        $taskText = $this->firstNonNull([
            $task,
            $subjectAudit,
            $productName,
        ]) ?: '-';

        $pic = $this->firstNonNull([
            $this->nullIfEmpty($mapped['pic'] ?? null),
            $division,
        ]) ?: '-';

        $createdAt = $this->parseDate($mapped['original_commitment_date'] ?? null) ?: $this->parseDate($mapped['update_commitment_date'] ?? null);
        $targetCloseDate = $this->parseDate($mapped['update_commitment_date'] ?? null) ?: $this->parseDate($mapped['original_commitment_date'] ?? null);
        $closedAt = $statusLabel === 'Closed' ? $targetCloseDate : null;

        $year = $this->resolveYear($mapped['year'] ?? null, $createdAt, $targetCloseDate);
        $riskId = $this->firstNonNull([
            $this->nullIfEmpty($mapped['log_css'] ?? null),
            $this->nullIfEmpty($mapped['ref_no'] ?? null),
            $this->nullIfEmpty($mapped['nomor'] ?? null),
        ]) ?: ('IMPORT-' . $rowIndex);

        $overdueItems = $this->buildOverdueItems(
            $mapped['aging'] ?? null,
            $mapped['maturity_days'] ?? null,
            $targetCloseDate
        );

        return [
            'risk_db_id' => $rowIndex,
            'risk_id' => (string) $riskId,
            'bank' => $bank,
            'year' => $year,
            'auditee' => (string) $auditee,
            'division' => (string) $division,
            'product' => (string) $product,
            'product_name' => (string) $productName,
            'status' => $statusLabel,
            'status_exit' => $statusLabel,
            'status_detail' => (string) $statusDetail,
            'task' => (string) $taskText,
            'pic' => (string) $pic,
            'assigned_to' => (string) $pic,
            'created_at' => is_null($createdAt) ? null : $createdAt->format('Y-m-d'),
            'closed_at' => is_null($closedAt) ? null : $closedAt->format('Y-m-d'),
            'target_close_date' => is_null($targetCloseDate) ? null : $targetCloseDate->format('Y-m-d'),
            'overdue_items' => $overdueItems,
        ];
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private function firstNonNull(array $values): ?string
    {
        foreach ($values as $value) {
            if (!is_null($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    private function normalizeStatusLabel(string $value): string
    {
        $value = strtolower(trim($value));
        if ($value === '') {
            return 'On Progress';
        }

        if (Str::contains($value, 'domain')) {
            return 'Domain Bank';
        }

        if (Str::contains($value, ['closed', 'close', 'done', 'resolved', 'selesai'])) {
            return 'Closed';
        }

        return 'On Progress';
    }

    /**
     * @param  mixed  $value
     */
    private function nullIfEmpty($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $text = trim((string) $value);
        $text = str_replace(["\r", "\n", "\t"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim((string) $text);

        if ($text === '') {
            return null;
        }

        $normalized = strtoupper($text);
        if (in_array($normalized, ['NO DATA', 'N/A', 'NULL', '-'], true)) {
            return null;
        }

        return $text;
    }

    /**
     * @param  mixed  $value
     */
    private function parseDate($value): ?Carbon
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return (clone $value)->startOfDay();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->startOfDay();
        }

        if (is_numeric($value)) {
            $number = (float) $value;

            if ($number > 1000 && $number < 60000) {
                try {
                    return Carbon::instance(ExcelDate::excelToDateTimeObject($number))->startOfDay();
                } catch (\Throwable $e) {
                    // continue with another parser.
                }
            }

            $year = (int) $number;
            if ($year >= 2000 && $year <= 2100) {
                return Carbon::create($year, 1, 1)->startOfDay();
            }
        }

        $text = $this->nullIfEmpty($value);
        if (is_null($text)) {
            return null;
        }

        $text = $this->translateIndonesianMonth($text);
        $formats = ['d/m/Y', 'd-m-Y', 'j/n/Y', 'j-n-Y', 'Y-m-d', 'Y/m/d', 'j F Y', 'd F Y'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $text)->startOfDay();
            } catch (\Throwable $e) {
                // continue.
            }
        }

        try {
            return Carbon::parse($text)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function translateIndonesianMonth(string $text): string
    {
        $replace = [
            'januari' => 'January',
            'februari' => 'February',
            'maret' => 'March',
            'april' => 'April',
            'mei' => 'May',
            'juni' => 'June',
            'juli' => 'July',
            'agustus' => 'August',
            'september' => 'September',
            'oktober' => 'October',
            'november' => 'November',
            'desember' => 'December',
        ];

        $result = $text;
        foreach ($replace as $key => $value) {
            $result = preg_replace('/\b' . $key . '\b/i', $value, $result) ?: $result;
        }

        return $result;
    }

    /**
     * @param  mixed  $value
     */
    private function resolveYear($value, ?Carbon $createdAt, ?Carbon $targetCloseDate): int
    {
        if (is_numeric($value)) {
            $year = (int) $value;
            if ($year >= 2000 && $year <= 2100) {
                return $year;
            }
        }

        $text = $this->nullIfEmpty($value);
        if (!is_null($text) && preg_match('/(20\d{2})/', $text, $matches) === 1) {
            $candidate = (int) ($matches[1] ?? 0);
            if ($candidate >= 2000 && $candidate <= 2100) {
                return $candidate;
            }
        }

        if ($createdAt instanceof Carbon) {
            return (int) $createdAt->format('Y');
        }

        if ($targetCloseDate instanceof Carbon) {
            return (int) $targetCloseDate->format('Y');
        }

        return (int) now()->format('Y');
    }

    /**
     * @param  mixed  $agingValue
     * @param  mixed  $maturityDaysValue
     * @return array<int, array{bucket: string, months_overdue: int, deadline: string}>
     */
    private function buildOverdueItems($agingValue, $maturityDaysValue, ?Carbon $targetCloseDate): array
    {
        $bucket = $this->resolveAgingBucket($agingValue);
        $months = $this->resolveMonthsOverdue($maturityDaysValue, $bucket, $targetCloseDate);

        if (is_null($bucket) && !is_null($months)) {
            $bucket = $this->resolveBucketFromMonths($months);
        }

        if (is_null($bucket)) {
            return [];
        }

        if (is_null($months) || $months <= 0) {
            $months = $this->defaultMonthsByBucket($bucket);
        }

        return [[
            'bucket' => $bucket,
            'months_overdue' => max(1, (int) $months),
            'deadline' => is_null($targetCloseDate) ? '' : $targetCloseDate->format('Y-m-d'),
        ]];
    }

    /**
     * @param  mixed  $value
     */
    private function resolveAgingBucket($value): ?string
    {
        $text = $this->nullIfEmpty($value);
        if (is_null($text)) {
            return null;
        }

        $normalized = strtolower($text);
        if (Str::contains($normalized, ['no data', 'n/a', 'null'])) {
            return null;
        }

        if (preg_match('/>\s*9/', $normalized) === 1) {
            return '>9 Bulan';
        }

        if (preg_match('/<=?\s*9/', $normalized) === 1) {
            return '<=9 Bulan';
        }

        if (preg_match('/<=?\s*(3|6)/', $normalized) === 1) {
            return '<=6 Bulan';
        }

        if (preg_match('/(\d+)/', $normalized, $matches) === 1) {
            $months = (int) ($matches[1] ?? 0);
            if ($months > 0) {
                return $this->resolveBucketFromMonths($months);
            }
        }

        return null;
    }

    /**
     * @param  mixed  $value
     */
    private function resolveMonthsOverdue($value, ?string $bucket, ?Carbon $targetCloseDate): ?int
    {
        if (is_numeric($value)) {
            $days = (int) floor((float) $value);
            if ($days > 0) {
                return max(1, (int) floor($days / 30));
            }
        }

        if ($targetCloseDate instanceof Carbon && $targetCloseDate->lessThan(Carbon::today())) {
            return max(1, (int) floor($targetCloseDate->diffInDays(Carbon::today()) / 30));
        }

        if (!is_null($bucket)) {
            return $this->defaultMonthsByBucket($bucket);
        }

        return null;
    }

    private function resolveBucketFromMonths(int $months): string
    {
        if ($months <= 6) {
            return '<=6 Bulan';
        }

        if ($months <= 9) {
            return '<=9 Bulan';
        }

        return '>9 Bulan';
    }

    private function defaultMonthsByBucket(string $bucket): int
    {
        if ($bucket === '<=6 Bulan') {
            return 3;
        }

        if ($bucket === '<=9 Bulan') {
            return 7;
        }

        return 10;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function buildMeta(array $rows, string $sheetName, int $headerRow): array
    {
        $statusCounts = [
            'Closed' => 0,
            'Domain Bank' => 0,
            'On Progress' => 0,
        ];

        $banks = [];
        $years = [];

        foreach ($rows as $row) {
            $status = $this->normalizeStatusLabel((string) ($row['status_exit'] ?? $row['status'] ?? 'On Progress'));
            $statusCounts[$status] = (int) ($statusCounts[$status] ?? 0) + 1;

            $bank = strtoupper(trim((string) ($row['bank'] ?? '')));
            if ($bank !== '') {
                $banks[$bank] = true;
            }

            $year = (int) ($row['year'] ?? 0);
            if ($year >= 2000 && $year <= 2100) {
                $years[$year] = true;
            }
        }

        $yearList = array_keys($years);
        rsort($yearList, SORT_NUMERIC);

        return [
            'sheet_name' => $sheetName,
            'header_row' => $headerRow,
            'row_count' => count($rows),
            'status_counts' => $statusCounts,
            'banks' => array_values(array_keys($banks)),
            'years' => $yearList,
        ];
    }
}

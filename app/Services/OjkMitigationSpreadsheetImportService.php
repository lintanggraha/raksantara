<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OjkMitigationSpreadsheetImportService
{
    /**
     * @return array{
     *   rows: array<int, array<string, mixed>>,
     *   meta: array<string, mixed>
     * }
     */
    public function parse(string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new \RuntimeException('File tidak ditemukan.');
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (\Throwable $e) {
            throw new \RuntimeException('File Excel tidak dapat dibaca.', 0, $e);
        }

        $sheet = $this->resolveWorksheet($spreadsheet);
        [$headerRow, $map] = $this->resolveHeaderMap($sheet);
        $highestRow = (int) $sheet->getHighestDataRow();
        $highestColumn = (string) $sheet->getHighestDataColumn();

        $mappedRows = [];
        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $cells = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, true, false)[0];
            $mapped = $this->extractMappedRow($cells, $map);
            $mappedRows[] = [
                'row_index' => $row,
                'mapped' => $mapped,
            ];
        }

        $groups = $this->groupMappedRowsByActionPlan($mappedRows);
        $rows = $this->expandActionPlanGroups($groups);

        if (empty($rows)) {
            throw new \RuntimeException('Tidak ada data valid pada file import.');
        }

        return [
            'rows' => $rows,
            'meta' => [
                'sheet_name' => $sheet->getTitle(),
                'header_row' => $headerRow,
                'row_count' => count($rows),
            ],
        ];
    }

    private function resolveWorksheet(Spreadsheet $spreadsheet): Worksheet
    {
        $sheet = $spreadsheet->getSheetByName('Sheet1');
        if ($sheet instanceof Worksheet) {
            return $sheet;
        }

        return $spreadsheet->getActiveSheet();
    }

    /**
     * @return array{0: int, 1: array<string, int>}
     */
    private function resolveHeaderMap(Worksheet $sheet): array
    {
        $highestRow = min(20, (int) $sheet->getHighestDataRow());
        $highestColumn = (string) $sheet->getHighestDataColumn();

        $bestRow = 0;
        $bestScore = -1;
        $bestMap = [];

        for ($row = 1; $row <= $highestRow; $row++) {
            $cells = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, false, false, false)[0];
            $map = [];

            foreach ($cells as $index => $headerRaw) {
                $header = $this->normalizeHeader((string) $headerRaw);
                if ($header === '') {
                    continue;
                }

                $key = $this->detectHeaderKey($header);
                if (!is_null($key) && !array_key_exists($key, $map)) {
                    $map[$key] = $index;
                }
            }

            $score = count(array_intersect(array_keys($map), ['item_number', 'action_plan', 'pic', 'solution', 'target']));
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestRow = $row;
                $bestMap = $map;
            }
        }

        if ($bestScore < 3 || !array_key_exists('action_plan', $bestMap)) {
            throw new \RuntimeException('Header file OJK tidak dikenali. Pastikan kolom NO, ACTION PLAN, PIC, SOLUSI, TARGET ada.');
        }

        return [$bestRow, $bestMap];
    }

    private function normalizeHeader(string $header): string
    {
        $text = trim($header);
        if ($text === '') {
            return '';
        }

        $text = str_replace(["\r", "\n", "\t"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return strtolower(trim((string) $text));
    }

    private function detectHeaderKey(string $header): ?string
    {
        if ($header === 'no' || Str::contains($header, ['no.', 'nomor'])) {
            return 'item_number';
        }
        if (Str::contains($header, ['action plan', 'aksi', 'rencana'])) {
            return 'action_plan';
        }
        if (Str::contains($header, ['pic'])) {
            return 'pic';
        }
        if (Str::contains($header, ['solusi', 'solution'])) {
            return 'solution';
        }
        if (Str::contains($header, ['target', 'deadline'])) {
            return 'target';
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $cells
     * @param  array<string, int>  $map
     * @return array<string, mixed>
     */
    private function extractMappedRow(array $cells, array $map): array
    {
        $result = [];
        foreach ($map as $key => $index) {
            $result[$key] = $cells[$index] ?? null;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $mapped
     * @return array<string, mixed>|null
     */
    private function prepareRow(array $mapped, int $rowNumber): ?array
    {
        $itemNumber = $this->cleanText($mapped['item_number'] ?? null);
        $actionPlan = $this->cleanText($mapped['action_plan'] ?? null);
        $pic = $this->cleanText($mapped['pic'] ?? null);
        $solution = $this->cleanText($mapped['solution'] ?? null);
        $target = $this->cleanText($mapped['target'] ?? null);

        if (is_null($actionPlan) && is_null($pic) && is_null($solution) && is_null($target)) {
            return null;
        }

        if (!is_null($actionPlan) && Str::contains(strtoupper($actionPlan), 'TOTAL')) {
            return null;
        }

        $sortOrder = $rowNumber;
        if (!is_null($itemNumber) && preg_match('/\d+/', $itemNumber, $match) === 1) {
            $sortOrder = (int) $match[0];
        }

        $status = $this->inferStatus($actionPlan, $solution, $target);
        $targetDate = $this->extractTargetDate($mapped['target'] ?? null, $target);

        return [
            'item_number' => $itemNumber ?? (string) $sortOrder,
            'action_plan' => $actionPlan ?? '-',
            'pic' => $pic,
            'solution' => $solution,
            'target' => $target,
            'target_date' => is_null($targetDate) ? null : $targetDate->toDateString(),
            'mitigation_status' => $status,
            'sort_order' => $sortOrder,
        ];
    }

    /**
     * @param  mixed  $value
     */
    private function cleanText($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim((string) $text);

        if ($text === '') {
            return null;
        }

        return $text;
    }

    private function inferStatus(?string $actionPlan, ?string $solution, ?string $target): string
    {
        $text = strtolower(trim(implode(' ', array_filter([
            $actionPlan,
            $solution,
            $target,
        ]))));

        $doneKeywords = ['done', 'selesai', 'closed', 'completed', 'complete'];

        if ($text === '') {
            return 'On Progress';
        }

        if (Str::contains($text, ['blocked', 'hold', 'tertunda'])) {
            return 'Blocked';
        }

        if (Str::contains($text, ['belum', 'not started'])) {
            return 'Not Started';
        }

        $targetLines = $this->splitLines($target);
        if (!empty($targetLines)) {
            $doneLines = 0;
            $datedLines = 0;

            foreach ($targetLines as $line) {
                $lineLower = strtolower($line);
                if (Str::contains($lineLower, $doneKeywords)) {
                    $doneLines++;
                }
                if ($this->lineContainsDate($lineLower)) {
                    $datedLines++;
                }
            }

            if ($doneLines > 0 && $datedLines === 0 && $doneLines === count($targetLines)) {
                return 'Done';
            }

            if ($doneLines > 0 && $datedLines > 0) {
                return 'On Progress';
            }
        }

        if (Str::contains($text, $doneKeywords)) {
            return 'Done';
        }

        return 'On Progress';
    }

    /**
     * @param  mixed  $rawTarget
     */
    private function extractTargetDate($rawTarget, ?string $targetText): ?Carbon
    {
        if (is_numeric($rawTarget)) {
            $excelNumeric = (float) $rawTarget;
            if ($excelNumeric > 1000 && $excelNumeric < 60000) {
                try {
                    return Carbon::instance(ExcelDate::excelToDateTimeObject($excelNumeric))->startOfDay();
                } catch (\Throwable $e) {
                    // fallback to text parsing
                }
            }
        }

        if (is_null($targetText) || trim($targetText) === '') {
            return null;
        }

        $text = $this->translateIndonesianMonths($targetText);
        $text = str_replace(["\r", "\n"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim((string) $text);

        $dates = [];

        preg_match_all('/\b(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})\b/', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            try {
                $dates[] = Carbon::createFromFormat('j F Y', $match[1] . ' ' . $match[2] . ' ' . $match[3])->startOfDay();
            } catch (\Throwable $e) {
                // continue
            }
        }

        preg_match_all('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/', $text, $numericMatches, PREG_SET_ORDER);
        foreach ($numericMatches as $match) {
            try {
                $dates[] = Carbon::createFromFormat('j-n-Y', $match[1] . '-' . $match[2] . '-' . $match[3])->startOfDay();
            } catch (\Throwable $e) {
                // continue
            }
        }

        if (empty($dates)) {
            if (preg_match('/\d/', $text)) {
                try {
                    return Carbon::parse($text)->startOfDay();
                } catch (\Throwable $e) {
                    return null;
                }
            }

            return null;
        }

        usort($dates, function (Carbon $a, Carbon $b) {
            return $a->lessThan($b) ? -1 : ($a->greaterThan($b) ? 1 : 0);
        });

        return end($dates) ?: null;
    }

    private function translateIndonesianMonths(string $text): string
    {
        $map = [
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
        foreach ($map as $id => $en) {
            $result = preg_replace('/\b' . $id . '\b/i', $en, $result) ?: $result;
        }

        return $result;
    }

    /**
     * @param  array<int, array{row_index: int, mapped: array<string, mixed>}>  $mappedRows
     * @return array<int, array{
     *   base_number: string,
     *   action_plan: string,
     *   pic: string|null,
     *   row_index: int,
     *   line_items: array<int, array{solution: string|null, target: string|null}>
     * }>
     */
    private function groupMappedRowsByActionPlan(array $mappedRows): array
    {
        $groups = [];
        $current = null;

        foreach ($mappedRows as $raw) {
            $rowIndex = (int) ($raw['row_index'] ?? 0);
            $mapped = is_array($raw['mapped'] ?? null) ? $raw['mapped'] : [];

            $itemNumber = $this->cleanText($mapped['item_number'] ?? null);
            $actionPlan = $this->cleanText($mapped['action_plan'] ?? null);
            $pic = $this->cleanText($mapped['pic'] ?? null);
            $solution = $this->cleanText($mapped['solution'] ?? null);
            $target = $this->cleanText($mapped['target'] ?? null);

            if (is_null($itemNumber) && is_null($actionPlan) && is_null($pic) && is_null($solution) && is_null($target)) {
                continue;
            }

            if (!is_null($actionPlan) && Str::contains(strtoupper($actionPlan), 'TOTAL')) {
                continue;
            }

            $isContinuation = is_null($itemNumber) && is_null($actionPlan) && is_null($pic);
            if ($isContinuation && is_array($current)) {
                if (!is_null($solution) || !is_null($target)) {
                    $current['line_items'][] = [
                        'solution' => $solution,
                        'target' => $target,
                    ];
                }
                continue;
            }

            if (is_array($current)) {
                $groups[] = $current;
                $current = null;
            }

            $baseNumber = !is_null($itemNumber) ? $itemNumber : (string) $rowIndex;
            $current = [
                'base_number' => $baseNumber,
                'action_plan' => $actionPlan ?? '-',
                'pic' => $pic,
                'row_index' => $rowIndex,
                'line_items' => [],
            ];

            if (!is_null($solution) || !is_null($target)) {
                $current['line_items'][] = [
                    'solution' => $solution,
                    'target' => $target,
                ];
            }
        }

        if (is_array($current)) {
            $groups[] = $current;
        }

        return $groups;
    }

    /**
     * @param  array<int, array{
     *   base_number: string,
     *   action_plan: string,
     *   pic: string|null,
     *   row_index: int,
     *   line_items: array<int, array{solution: string|null, target: string|null}>
     * }>  $groups
     * @return array<int, array<string, mixed>>
     */
    private function expandActionPlanGroups(array $groups): array
    {
        $rows = [];
        $sortOrder = 1;

        foreach ($groups as $group) {
            $entries = $this->buildLineEntries($group['line_items'] ?? []);
            if (empty($entries)) {
                $entries[] = [
                    'solution' => null,
                    'target' => null,
                    'suffix' => null,
                ];
            }

            $isMultiple = count($entries) > 1;

            foreach ($entries as $index => $entry) {
                $solution = is_string($entry['solution'] ?? null) ? $entry['solution'] : null;
                $target = is_string($entry['target'] ?? null) ? $entry['target'] : null;
                $suffix = is_string($entry['suffix'] ?? null) ? $entry['suffix'] : null;

                if (is_null($solution) && is_null($target)) {
                    continue;
                }

                $itemNumber = $this->composeItemNumber(
                    (string) ($group['base_number'] ?? ''),
                    $suffix,
                    $isMultiple,
                    $index
                );

                $targetDate = $this->extractTargetDate(null, $target);
                $status = $this->inferStatus(
                    (string) ($group['action_plan'] ?? '-'),
                    $solution,
                    $target
                );

                $rows[] = [
                    'item_number' => $itemNumber,
                    'action_plan' => (string) ($group['action_plan'] ?? '-'),
                    'pic' => $group['pic'] ?? null,
                    'solution' => $solution,
                    'target' => $target,
                    'target_date' => is_null($targetDate) ? null : $targetDate->toDateString(),
                    'mitigation_status' => $status,
                    'sort_order' => $sortOrder,
                ];
                $sortOrder++;
            }
        }

        return $rows;
    }

    /**
     * @param  array<int, array{solution: string|null, target: string|null}>  $lineItems
     * @return array<int, array{solution: string|null, target: string|null, suffix: string|null}>
     */
    private function buildLineEntries(array $lineItems): array
    {
        $entries = [];

        foreach ($lineItems as $line) {
            $solutionSegments = $this->splitSegmentedText($line['solution'] ?? null);
            $targetSegments = $this->splitSegmentedText($line['target'] ?? null);
            $segmentCount = max(count($solutionSegments), count($targetSegments), 1);

            for ($i = 0; $i < $segmentCount; $i++) {
                $solution = $this->segmentContentAt($solutionSegments, $i);
                $target = $this->segmentContentAt($targetSegments, $i);
                $suffix = $this->segmentLabelAt($targetSegments, $i) ?? $this->segmentLabelAt($solutionSegments, $i);

                if (is_null($solution) && is_null($target)) {
                    continue;
                }

                $entries[] = [
                    'solution' => $solution,
                    'target' => $target,
                    'suffix' => $suffix,
                ];
            }
        }

        return $entries;
    }

    /**
     * @return array<int, array{label: string|null, content: string}>
     */
    private function splitSegmentedText(?string $text): array
    {
        $text = $this->cleanText($text);
        if (is_null($text)) {
            return [];
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $parts = preg_split('/\n(?=\s*[a-zA-Z]\.\s*)/', $normalized) ?: [$normalized];
        $segments = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }

            $label = null;
            $content = $part;

            if (preg_match('/^\s*([a-zA-Z])\.\s*(.*)$/s', $part, $matches) === 1) {
                $label = strtolower(trim((string) ($matches[1] ?? '')));
                $content = trim((string) ($matches[2] ?? ''));
                if ($content === '') {
                    $content = $part;
                }
            }

            $segments[] = [
                'label' => $label,
                'content' => $content,
            ];
        }

        return $segments;
    }

    /**
     * @param  array<int, array{label: string|null, content: string}>  $segments
     */
    private function segmentContentAt(array $segments, int $index): ?string
    {
        if (isset($segments[$index]['content'])) {
            return $segments[$index]['content'];
        }

        if (count($segments) === 1) {
            return $segments[0]['content'];
        }

        return null;
    }

    /**
     * @param  array<int, array{label: string|null, content: string}>  $segments
     */
    private function segmentLabelAt(array $segments, int $index): ?string
    {
        if (isset($segments[$index]['label'])) {
            return $segments[$index]['label'];
        }

        if (count($segments) === 1) {
            return $segments[0]['label'];
        }

        return null;
    }

    private function composeItemNumber(string $base, ?string $suffix, bool $isMultiple, int $index): string
    {
        $base = trim($base);
        if ($base === '') {
            $base = (string) ($index + 1);
        }

        if (!$isMultiple) {
            return $base;
        }

        $suffix = trim((string) $suffix);
        if ($suffix !== '') {
            $normalizedSuffix = strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $suffix));
            if ($normalizedSuffix !== '') {
                return $base . $normalizedSuffix;
            }
        }

        return $base . '-' . ($index + 1);
    }

    /**
     * @return array<int, string>
     */
    private function splitLines(?string $text): array
    {
        if (is_null($text)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];

        return array_values(array_filter(array_map(function ($line) {
            return trim((string) $line);
        }, $lines), function ($line) {
            return $line !== '';
        }));
    }

    private function lineContainsDate(string $line): bool
    {
        $normalized = $this->translateIndonesianMonths($line);

        return preg_match('/\b(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})\b/', $normalized) === 1
            || preg_match('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/', $normalized) === 1;
    }
}

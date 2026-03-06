<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetReportExport implements WithMultipleSheets
{
    /** @var array */
    private $sections;

    public function __construct(array $sections)
    {
        $this->sections = $sections;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->sections as $index => $section) {
            if (!is_array($section)) {
                continue;
            }

            $title = trim((string) ($section['title'] ?? 'Sheet ' . ($index + 1)));
            $headings = is_array($section['headings'] ?? null) ? array_values($section['headings']) : [];
            $rows = is_array($section['rows'] ?? null) ? array_values($section['rows']) : [];
            $rows = $this->normalizeRows($rows);

            if (empty($headings)) {
                $headings = $this->generateFallbackHeadings($rows);
            }

            $sheets[] = new ArraySheetExport(
                $title,
                $headings,
                $rows
            );
        }

        if (empty($sheets)) {
            $sheets[] = new ArraySheetExport('Report', ['Info'], [['No report data available.']]);
        }

        return $sheets;
    }

    private function normalizeRows(array $rows): array
    {
        $normalizedRows = [];

        foreach ($rows as $row) {
            if (is_array($row)) {
                $normalizedRows[] = array_values($row);
                continue;
            }

            $normalizedRows[] = [(string) $row];
        }

        return $normalizedRows;
    }

    private function generateFallbackHeadings(array $rows): array
    {
        if (empty($rows)) {
            return ['Value'];
        }

        $columnCount = 1;
        foreach ($rows as $row) {
            if (is_array($row)) {
                $columnCount = max($columnCount, count($row));
            }
        }

        $headings = [];
        for ($index = 1; $index <= $columnCount; $index++) {
            $headings[] = 'Column ' . $index;
        }

        return $headings;
    }
}

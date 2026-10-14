<?php

namespace App\Support;

class PdfReportLayout
{
    /**
     * Build PDF layout configuration based on section width profile.
     *
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<string, mixed>
     */
    public static function resolve(array $sections): array
    {
        $maxColumns = self::maxColumnCount($sections);
        $paper = $maxColumns >= 10 ? 'A3' : 'A4';
        $density = $maxColumns >= 12
            ? 'ultra'
            : ($maxColumns >= 9 ? 'compact' : 'normal');

        $horizontalMargin = $paper === 'A3' ? '10px' : ($maxColumns >= 8 ? '12px' : '18px');

        return [
            'paper' => $paper,
            'orientation' => 'landscape',
            'density' => $density,
            'max_columns' => $maxColumns,
            'page_margin' => [
                'top' => '108px',
                'right' => $horizontalMargin,
                'bottom' => '68px',
                'left' => $horizontalMargin,
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     */
    public static function maxColumnCount(array $sections): int
    {
        $maxColumns = 1;

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $maxColumns = max($maxColumns, self::columnCountForSection($section));
        }

        return max(1, $maxColumns);
    }

    /**
     * @param  array<string, mixed>  $section
     */
    public static function columnCountForSection(array $section): int
    {
        $headings = is_array($section['headings'] ?? null) ? array_values($section['headings']) : [];
        $rows = is_array($section['rows'] ?? null) ? array_values($section['rows']) : [];
        $eventRows = is_array($section['event_rows'] ?? null) ? array_values($section['event_rows']) : [];

        $columnCount = count($headings);

        if ($columnCount === 0 && isset($rows[0]) && is_array($rows[0])) {
            $columnCount = count(array_values($rows[0]));
        }

        if ($columnCount === 0 && isset($eventRows[0]) && is_array($eventRows[0])) {
            $columnCount = count(array_values($eventRows[0]));
        }

        return max(1, $columnCount);
    }

    public static function tableDensityClass(int $columnCount): string
    {
        if ($columnCount >= 12) {
            return 'table-density-ultra';
        }

        if ($columnCount >= 9) {
            return 'table-density-compact';
        }

        if ($columnCount >= 7) {
            return 'table-density-medium';
        }

        return 'table-density-normal';
    }
}

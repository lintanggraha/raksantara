<?php

namespace App\Support;

class PdfSectionPaginator
{
    /**
     * Section types with custom layouts that should not be split by this paginator.
     *
     * @var array<int, string>
     */
    private const UNSPLITTABLE_TYPES = [
        'monitoring_overview',
        'board_executive_summary',
        'board_status_chart',
        'board_category_chart',
        'board_trend_chart',
        'heatmap_dashboard',
    ];

    /**
     * Split tabular sections into predictable page-friendly chunks.
     *
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<int, array<string, mixed>>
     */
    public static function paginate(array $sections): array
    {
        $result = [];

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $type = (string) ($section['type'] ?? '');
            $rows = is_array($section['rows'] ?? null) ? array_values($section['rows']) : [];

            if (empty($rows) || in_array($type, self::UNSPLITTABLE_TYPES, true)) {
                $result[] = $section;
                continue;
            }

            $chunkSize = self::resolveChunkSize($section, $rows);
            if ($chunkSize <= 0 || count($rows) <= $chunkSize) {
                $result[] = $section;
                continue;
            }

            $chunks = array_chunk($rows, $chunkSize);
            $totalChunks = count($chunks);
            $baseTitle = (string) ($section['title'] ?? 'Section');

            foreach ($chunks as $index => $chunkRows) {
                $chunkedSection = $section;
                $chunkedSection['rows'] = $chunkRows;
                $chunkedSection['title'] = self::withPartSuffix($baseTitle, $index + 1, $totalChunks);
                $result[] = $chunkedSection;
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $section
     * @param  array<int, mixed>  $rows
     */
    private static function resolveChunkSize(array $section, array $rows): int
    {
        $headings = is_array($section['headings'] ?? null) ? array_values($section['headings']) : [];
        $columnCount = count($headings);

        if ($columnCount === 0 && isset($rows[0]) && is_array($rows[0])) {
            $columnCount = count(array_values($rows[0]));
        }

        $columnCount = max(1, $columnCount);

        if ($columnCount >= 9) {
            return 12;
        }

        if ($columnCount >= 7) {
            return 14;
        }

        if ($columnCount >= 5) {
            return 16;
        }

        return 20;
    }

    private static function withPartSuffix(string $title, int $part, int $total): string
    {
        if ($total <= 1) {
            return $title;
        }

        return sprintf('%s (Part %d/%d)', $title, $part, $total);
    }
}

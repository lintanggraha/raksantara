<?php

namespace Tests\Unit;

use App\Support\PdfReportLayout;
use PHPUnit\Framework\TestCase;

class PdfReportLayoutTest extends TestCase
{
    public function test_resolve_uses_a3_for_wide_sections(): void
    {
        $sections = [[
            'title' => 'Risk Register',
            'headings' => [
                'Risk ID',
                'Year',
                'Risk Title',
                'Category',
                'Cause Category',
                'Risk Owner',
                'Status',
                'Inherent Risk',
                'Inherent Level',
                'Residual Risk',
                'Residual Level',
            ],
            'rows' => [
                ['R-1', '2026', 'Sample', 'Cat', 'Cause', 'Owner', 'Open', '12', 'High', '8', 'Moderate'],
            ],
        ]];

        $layout = PdfReportLayout::resolve($sections);

        $this->assertSame('A3', $layout['paper']);
        $this->assertSame('landscape', $layout['orientation']);
        $this->assertSame(11, $layout['max_columns']);
        $this->assertSame('compact', $layout['density']);
    }

    public function test_resolve_uses_a4_for_narrow_sections(): void
    {
        $sections = [[
            'title' => 'Executive Summary',
            'headings' => ['Metric', 'Value'],
            'rows' => [
                ['Total Risks', '15'],
                ['Closed Risks', '8'],
            ],
        ]];

        $layout = PdfReportLayout::resolve($sections);

        $this->assertSame('A4', $layout['paper']);
        $this->assertSame(2, $layout['max_columns']);
        $this->assertSame('normal', $layout['density']);
    }

    public function test_table_density_class_maps_to_column_thresholds(): void
    {
        $this->assertSame('table-density-normal', PdfReportLayout::tableDensityClass(4));
        $this->assertSame('table-density-medium', PdfReportLayout::tableDensityClass(7));
        $this->assertSame('table-density-compact', PdfReportLayout::tableDensityClass(9));
        $this->assertSame('table-density-ultra', PdfReportLayout::tableDensityClass(12));
    }
}

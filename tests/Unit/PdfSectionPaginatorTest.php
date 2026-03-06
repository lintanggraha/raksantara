<?php

namespace Tests\Unit;

use App\Support\PdfSectionPaginator;
use PHPUnit\Framework\TestCase;

class PdfSectionPaginatorTest extends TestCase
{
    public function test_paginate_splits_large_tabular_sections_into_part_chunks(): void
    {
        $rows = [];
        for ($i = 1; $i <= 17; $i++) {
            $rows[] = ['Row ' . $i, 'Value ' . $i];
        }

        $sections = [[
            'title' => 'Audit Rekap',
            'type' => 'audit_rekap_table',
            'headings' => ['Label', 'Value'],
            'rows' => $rows,
        ]];

        $result = PdfSectionPaginator::paginate($sections);

        $this->assertCount(1, $result, 'Two-column section should not be split below default chunk size.');

        $rows[] = ['Row 18', 'Value 18'];
        $rows[] = ['Row 19', 'Value 19'];
        $rows[] = ['Row 20', 'Value 20'];
        $rows[] = ['Row 21', 'Value 21'];

        $sections[0]['rows'] = $rows;
        $result = PdfSectionPaginator::paginate($sections);

        $this->assertCount(2, $result);
        $this->assertSame('Audit Rekap (Part 1/2)', $result[0]['title']);
        $this->assertSame('Audit Rekap (Part 2/2)', $result[1]['title']);
        $this->assertCount(20, $result[0]['rows']);
        $this->assertCount(1, $result[1]['rows']);
    }

    public function test_paginate_does_not_split_special_chart_sections(): void
    {
        $rows = [];
        for ($i = 1; $i <= 40; $i++) {
            $rows[] = ['Status ' . $i, (string) $i];
        }

        $sections = [[
            'title' => 'Board Status',
            'type' => 'board_status_chart',
            'headings' => ['Status', 'Count'],
            'rows' => $rows,
        ]];

        $result = PdfSectionPaginator::paginate($sections);

        $this->assertCount(1, $result);
        $this->assertSame('Board Status', $result[0]['title']);
        $this->assertCount(40, $result[0]['rows']);
    }
}

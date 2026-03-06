<?php

namespace Tests\Unit;

use App\Services\OjkMitigationSpreadsheetImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;

class OjkMitigationSpreadsheetImportServiceTest extends TestCase
{
    public function test_parse_expands_continuation_rows_into_solution_level_entries(): void
    {
        $filePath = $this->buildSpreadsheetWithContinuationRows();

        $service = new OjkMitigationSpreadsheetImportService();
        $result = $service->parse($filePath);
        $rows = $result['rows'] ?? [];

        @unlink($filePath);

        $this->assertCount(3, $rows);

        $this->assertSame('1a', (string) ($rows[0]['item_number'] ?? ''));
        $this->assertSame('Solusi pertama', (string) ($rows[0]['solution'] ?? ''));
        $this->assertSame('Done', (string) ($rows[0]['target'] ?? ''));
        $this->assertNull($rows[0]['target_date']);
        $this->assertSame('Done', (string) ($rows[0]['mitigation_status'] ?? ''));

        $this->assertSame('1b', (string) ($rows[1]['item_number'] ?? ''));
        $this->assertSame('Solusi kedua', (string) ($rows[1]['solution'] ?? ''));
        $this->assertSame('10 Maret 2026', (string) ($rows[1]['target'] ?? ''));
        $this->assertSame('2026-03-10', (string) ($rows[1]['target_date'] ?? ''));
        $this->assertSame('On Progress', (string) ($rows[1]['mitigation_status'] ?? ''));

        $this->assertSame('2', (string) ($rows[2]['item_number'] ?? ''));
        $this->assertSame('Done', (string) ($rows[2]['mitigation_status'] ?? ''));
    }

    private function buildSpreadsheetWithContinuationRows(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        $sheet->setCellValue('A2', 'NO');
        $sheet->setCellValue('B2', 'ACTION PLAN');
        $sheet->setCellValue('C2', 'PIC');
        $sheet->setCellValue('D2', 'SOLUSI');
        $sheet->setCellValue('E2', 'TARGET');

        $sheet->setCellValue('A3', '1');
        $sheet->setCellValue('B3', 'Mitigasi A');
        $sheet->setCellValue('C3', 'Tim A');
        $sheet->setCellValue('D3', 'a. Solusi pertama');
        $sheet->setCellValue('E3', 'a. Done');

        $sheet->setCellValue('D4', 'b. Solusi kedua');
        $sheet->setCellValue('E4', 'b. 10 Maret 2026');

        $sheet->setCellValue('A5', '2');
        $sheet->setCellValue('B5', 'Mitigasi B');
        $sheet->setCellValue('C5', 'Tim B');
        $sheet->setCellValue('D5', 'Solusi B');
        $sheet->setCellValue('E5', 'Done');

        $tempPath = tempnam(sys_get_temp_dir(), 'ojk-parser-unit-');
        if ($tempPath === false) {
            throw new \RuntimeException('Unable to create temporary xlsx file.');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }
}

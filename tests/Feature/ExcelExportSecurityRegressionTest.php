<?php

namespace Tests\Feature;

use App\Exports\MultiSheetReportExport;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExcelExportSecurityRegressionTest extends TestCase
{
    public function test_formula_like_text_cells_are_exported_as_plain_strings(): void
    {
        $binary = Excel::raw(
            new MultiSheetReportExport([
                [
                    'title' => 'Security Probe',
                    'headings' => ['Payload', 'Numeric'],
                    'rows' => [
                        ['=2+2', 42],
                        ['  @SUM(1,1)', 7],
                        ['-10', 3],
                    ],
                ],
            ]),
            ExcelWriter::XLSX
        );

        $temporaryPath = tempnam(sys_get_temp_dir(), 'rms-export-');
        $this->assertNotFalse($temporaryPath);

        try {
            file_put_contents($temporaryPath, $binary);

            $spreadsheet = IOFactory::load($temporaryPath);
            $sheet = $spreadsheet->getActiveSheet();

            $this->assertSame('=2+2', $sheet->getCell('A2')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('A2')->getDataType());

            $this->assertSame('  @SUM(1,1)', $sheet->getCell('A3')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('A3')->getDataType());

            $this->assertSame('-10', $sheet->getCell('A4')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('A4')->getDataType());

            $this->assertSame(42, $sheet->getCell('B2')->getValue());
            $this->assertSame(DataType::TYPE_NUMERIC, $sheet->getCell('B2')->getDataType());
        } finally {
            if (is_string($temporaryPath) && is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }
}

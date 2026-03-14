<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ArraySheetExport implements FromArray, WithHeadings, WithColumnWidths, WithTitle, WithEvents
{
    private const COLUMN_SAMPLE_LIMIT = 300;
    private const CELL_TEXT_LIMIT = 120;

    /** @var string */
    private $title;

    /** @var array */
    private $headings;

    /** @var array */
    private $rows;

    public function __construct(string $title, array $headings, array $rows)
    {
        $this->title = $title;
        $this->headings = $headings;
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function columnWidths(): array
    {
        $columnCount = $this->determineColumnCount();
        $sampleRows = array_slice($this->rows, 0, self::COLUMN_SAMPLE_LIMIT);
        $widths = [];

        for ($column = 1; $column <= $columnCount; $column++) {
            $columnIndex = $column - 1;
            $maxLength = $this->measureCellLength($this->headings[$columnIndex] ?? '');

            foreach ($sampleRows as $row) {
                $maxLength = max($maxLength, $this->measureCellLength($this->extractColumnValue($row, $columnIndex)));
            }

            $columnLetter = Coordinate::stringFromColumnIndex($column);
            $widths[$columnLetter] = $this->resolveColumnWidth($maxLength, $columnCount);
        }

        return $widths;
    }

    public function title(): string
    {
        $title = preg_replace('/[\\\\\\/\\?\\*\\:\\[\\]]/', '-', $this->title);
        $title = trim((string) $title);

        if ($title === '') {
            $title = 'Sheet';
        }

        return substr($title, 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = max(1, (int) $sheet->getHighestRow());
                $headerRange = 'A1:' . $highestColumn . '1';
                $dataRange = 'A1:' . $highestColumn . $highestRow;

                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF0F172A'],
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => 'FFCBD5E1'],
                        ],
                    ],
                ];

                $sheet->freezePane('A2');
                $sheet->getStyle($headerRange)->applyFromArray($headerStyle);
                $sheet->setAutoFilter($headerRange);
                
                $sheet->getDefaultRowDimension()->setRowHeight(20);
                $sheet->getRowDimension(1)->setRowHeight(24);

                $sheet->getStyle($dataRange)
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $columnCount = Coordinate::columnIndexFromString($highestColumn);
                $pageSetup = $sheet->getPageSetup();
                $pageSetup->setOrientation(
                    $columnCount >= 8
                        ? PageSetup::ORIENTATION_LANDSCAPE
                        : PageSetup::ORIENTATION_PORTRAIT
                );
                $pageSetup->setFitToWidth(1);
                $pageSetup->setFitToHeight(0);
            },
        ];
    }

    private function determineColumnCount(): int
    {
        $columnCount = count($this->headings);
        $sampleRows = array_slice($this->rows, 0, self::COLUMN_SAMPLE_LIMIT);

        foreach ($sampleRows as $row) {
            if (is_array($row)) {
                $columnCount = max($columnCount, count(array_values($row)));
                continue;
            }

            $columnCount = max($columnCount, 1);
        }

        return max(1, $columnCount);
    }

    /**
     * @param  mixed  $row
     * @return mixed
     */
    private function extractColumnValue($row, int $columnIndex)
    {
        if (is_array($row)) {
            $values = array_values($row);

            return $values[$columnIndex] ?? '';
        }

        return $columnIndex === 0 ? $row : '';
    }

    /**
     * @param  mixed  $value
     */
    private function measureCellLength($value): int
    {
        $text = preg_replace('/\s+/', ' ', trim((string) $value));
        if ($text === '') {
            return 0;
        }

        if (function_exists('mb_strlen')) {
            return min(mb_strlen($text), self::CELL_TEXT_LIMIT);
        }

        return min(strlen($text), self::CELL_TEXT_LIMIT);
    }

    private function resolveColumnWidth(int $maxLength, int $columnCount): float
    {
        $baseWidth = (float) max(10, ceil(($maxLength + 2) * 1.05));

        if ($columnCount >= 12) {
            return (float) min(26, max(9, $baseWidth));
        }

        if ($columnCount >= 8) {
            return (float) min(34, max(10, $baseWidth));
        }

        return (float) min(48, max(12, $baseWidth));
    }
}

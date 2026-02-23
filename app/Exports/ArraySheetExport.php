<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ArraySheetExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
{
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

                // Freeze header row
                $sheet->freezePane('A2');

                // Apply header style (bold white text on dark blue background)
                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF1F4E78'],
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ];

                // Style the first row (headings)
                $sheet->getStyle('1:1')->applyFromArray($headerStyle);

                // Auto-filter on header row
                $highestColumn = $sheet->getHighestColumn();
                $sheet->setAutoFilter('A1:' . $highestColumn . '1');

                // Wrap text for all cells and vertical alignment
                $sheet->getStyle('A:'. $highestColumn)->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            },
        ];
    }
}

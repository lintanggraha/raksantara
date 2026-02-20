<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ArraySheetExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
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
}

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

        foreach ($this->sections as $section) {
            $sheets[] = new ArraySheetExport(
                $section['title'],
                $section['headings'],
                $section['rows']
            );
        }

        return $sheets;
    }
}

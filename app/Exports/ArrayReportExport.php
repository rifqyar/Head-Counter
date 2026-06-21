<?php

namespace App\Exports;

use App\Support\Reporting\SpreadsheetSafe;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class ArrayReportExport implements FromArray, WithHeadings, WithStrictNullComparison
{
    public function __construct(private readonly array $headings, private readonly array $rows) {}

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return array_map(fn ($row) => SpreadsheetSafe::row($row), $this->rows);
    }
}

<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/** A plain sheet: one headings row, then the given rows as-is. */
class ArrayExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    /**
     * @param  Collection<int, array<int, mixed>>  $rows
     * @param  array<int, string>  $headings
     */
    public function __construct(
        protected Collection $rows,
        protected array $headings,
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}

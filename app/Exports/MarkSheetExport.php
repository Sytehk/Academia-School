<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Mark entry template for one class/subject/exam. Its headings are exactly the
 * keys MarkSheetService::import() reads back, so the file round-trips.
 */
class MarkSheetExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array<int, int>  $slots  configured mark slots, e.g. [1, 2, 3]
     */
    public function __construct(
        protected Collection $rows,
        protected array $slots,
    ) {}

    public function collection(): Collection
    {
        return $this->rows->map(function (array $row) {
            $line = [$row['student_id'], $row['roll'], $row['student_name']];
            foreach ($this->slots as $slot) {
                $line[] = $row['mark_'.$slot];
            }
            $line[] = $row['is_absent'];

            return $line;
        });
    }

    public function headings(): array
    {
        return array_merge(
            ['student_id', 'roll', 'student_name'],
            array_map(fn ($slot) => 'mark_'.$slot, $this->slots),
            ['is_absent'],
        );
    }
}

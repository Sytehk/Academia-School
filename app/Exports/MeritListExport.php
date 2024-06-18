<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MeritListExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Merit Position', 'Student Name', 'Roll No', 'Class', 'Section', 'Total Marks', 'GPA', 'Grade'];
    }

    public function map($row): array
    {
        $session = $row['student_session'] ?? null;

        return [
            $row['position'] ?? '-',
            $row['student_name'] ?? '',
            $row['roll'] ?? '',
            $session?->class?->class_name ?? '',
            $session?->section?->section_name ?? '',
            is_numeric($row['total_marks'] ?? null) ? number_format((float) $row['total_marks'], 2) : '',
            is_numeric($row['gpa'] ?? null) ? number_format((float) $row['gpa'], 2) : '',
            $row['grade'] ?? '',
        ];
    }
}

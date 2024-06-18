<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Examination\Models\ExamMark;

class ExamResultExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Collection $examMarks) {}

    public function collection(): Collection
    {
        return $this->examMarks;
    }

    public function headings(): array
    {
        return ['Student', 'Roll', 'Class', 'Group', 'Subject', 'Exam', 'Total Marks', 'Grade', 'Grade Point'];
    }

    public function map($examMark): array
    {
        /** @var ExamMark $examMark */
        return [
            trim(($examMark->student->first_name ?? '').' '.($examMark->student->last_name ?? '')),
            $examMark->student?->studentSession?->roll,
            $examMark->class?->class_name,
            $examMark->group?->group_name,
            $examMark->subject?->subject_name,
            $examMark->exam?->name,
            $examMark->total_marks,
            $examMark->grade,
            $examMark->grade_point,
        ];
    }
}

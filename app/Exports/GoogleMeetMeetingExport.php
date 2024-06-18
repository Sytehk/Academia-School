<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Academic\Models\GoogleMeetMeeting;

class GoogleMeetMeetingExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Collection $meetings) {}

    public function collection(): Collection
    {
        return $this->meetings;
    }

    public function headings(): array
    {
        return ['#', 'Title', 'Teacher', 'Class', 'Section', 'Subject', 'Date', 'Start Time', 'End Time', 'Status', 'Meet Link'];
    }

    public function map($meeting): array
    {
        /** @var GoogleMeetMeeting $meeting */
        return [
            $meeting->id,
            $meeting->title,
            $meeting->teacher?->name,
            $meeting->classModel?->class_name,
            $meeting->section?->section_name,
            $meeting->subject?->subject_name,
            optional($meeting->start_date)->format('Y-m-d'),
            $meeting->start_time,
            $meeting->end_time,
            ucfirst($meeting->status),
            $meeting->meet_link,
        ];
    }
}

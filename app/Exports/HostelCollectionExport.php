<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Hostel\Models\HostelCollection;

class HostelCollectionExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Collection $collections) {}

    public function collection(): Collection
    {
        return $this->collections;
    }

    public function headings(): array
    {
        return ['Invoice No', 'Student', 'Hostel', 'Room', 'Month', 'Paid', 'Due', 'Date'];
    }

    public function map($collection): array
    {
        /** @var HostelCollection $collection */
        return [
            $collection->invoice_id,
            $collection->student?->user?->name,
            $collection->roomMember?->room?->hostel?->hostel_name,
            $collection->roomMember?->room?->room_number,
            $collection->month,
            $collection->total_paid,
            $collection->total_due,
            $collection->invoice_date,
        ];
    }
}

<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Transport\Models\TransportCollection;

class TransportCollectionExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Collection $collections) {}

    public function collection(): Collection
    {
        return $this->collections;
    }

    public function headings(): array
    {
        return ['Invoice No', 'Student', 'Route', 'Stop', 'Month', 'Fee', 'Discount', 'Fine', 'Paid', 'Due', 'Date'];
    }

    public function map($collection): array
    {
        /** @var TransportCollection $collection */
        return [
            $collection->invoice_id,
            $collection->student?->user?->name,
            $collection->transportMember?->route?->route_name,
            $collection->transportMember?->stop?->stop_name,
            $collection->month,
            $collection->fee_amount,
            $collection->discount,
            $collection->fine,
            $collection->total_paid,
            $collection->total_due,
            $collection->invoice_date,
        ];
    }
}

<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Academic\Models\WhatsAppLog;

class WhatsAppLogExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Collection $logs) {}

    public function collection(): Collection
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return ['#', 'Student', 'Phone', 'Message', 'Status', 'Retry Count', 'Sent At'];
    }

    public function map($log): array
    {
        /** @var WhatsAppLog $log */
        return [
            $log->id,
            $log->student?->user?->name,
            $log->phone,
            $log->message,
            ucfirst($log->status),
            $log->retry_count,
            $log->created_at?->format('Y-m-d H:i'),
        ];
    }
}

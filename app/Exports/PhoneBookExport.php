<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\SMS\Models\PhoneBook;

class PhoneBookExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Collection $phoneBooks) {}

    public function collection(): Collection
    {
        return $this->phoneBooks;
    }

    public function headings(): array
    {
        return ['Name', 'Phone', 'Category', 'Class', 'Section', 'Note'];
    }

    public function map($phoneBook): array
    {
        /** @var PhoneBook $phoneBook */
        return [
            $phoneBook->name,
            $phoneBook->phone,
            $phoneBook->category?->name,
            $phoneBook->class?->class_name,
            $phoneBook->section?->section_name,
            $phoneBook->note,
        ];
    }
}

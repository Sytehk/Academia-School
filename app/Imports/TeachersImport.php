<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeachersImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $isFullyBlank = collect($row)->every(fn ($value) => is_null($value) || trim((string) $value) === '');
            if ($isFullyBlank) {
                continue;
            }

            $this->data[] = [
                'roll_no' => trim((string) ($row['roll_no'] ?? '')),
                'name' => trim((string) ($row['name'] ?? '')),
                'gender' => $row['gender'] ?? null,
                'religion' => $row['religion'] ?? null,
                'phone' => trim((string) ($row['phone'] ?? '')),
                'email' => $row['email'] ?? null,
                'password' => $row['password'] ?? null,
            ];
        }
    }
}

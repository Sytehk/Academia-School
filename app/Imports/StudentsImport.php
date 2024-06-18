<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip only fully blank rows; rows with partially missing required
            // fields are still passed through so the import service can record
            // them as validation failures instead of silently dropping them.
            $isFullyBlank = collect($row)->every(fn ($value) => is_null($value) || trim((string) $value) === '');
            if ($isFullyBlank) {
                continue;
            }

            $this->data[] = [
                'roll_no' => trim((string) ($row['roll_no'] ?? '')),
                'name' => trim((string) ($row['name'] ?? '')),
                'registration_no' => $row['registration_no'] ?? null,
                'gender' => $row['gender'] ?? null,
                'religion' => $row['religion'] ?? null,
                'fathers_name' => $row['fathers_name'] ?? null,
                'mothers_name' => $row['mothers_name'] ?? null,
                'guardian_name' => $row['guardian_name'] ?? null,
                'guardian_number' => $row['guardian_number'] ?? null,
                'address' => $row['address'] ?? null,
                'mobile_no' => $row['mobile_no'] ?? null,
                'email' => $row['email'] ?? null,
                'password' => $row['password'] ?? null,
            ];
        }
    }
}

<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LibraryBooksImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Check if at least one required field is present
            if (
                isset($row['id']) && ! empty($row['id']) ||
                isset($row['code']) && ! empty($row['code']) ||
                isset($row['name']) && ! empty($row['name']) ||
                isset($row['author']) && ! empty($row['author'])
            ) {
                $this->data[] = [
                    'id' => $this->convertToUTF8($row['id'] ?? null),
                    'code' => $this->convertToUTF8($row['code'] ?? null),
                    'name' => $this->convertToUTF8($row['name'] ?? null),
                    'author' => $this->convertToUTF8($row['author'] ?? null),
                ];
            }
        }
    }

    protected function convertToUTF8($value)
    {
        return mb_convert_encoding($value, 'UTF-8', 'auto');
    }
}

<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsAttendanceImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row['student_id']) {
                $excelStartDate = $row['entry_time'];
                $excelEndDate = $row['exit_time'];

                if (is_numeric($excelStartDate) && is_numeric($excelEndDate)) {
                    $unixTimestampStartDate = ($excelStartDate - 25569) * 86400;
                    $carbonStartDate = Carbon::createFromTimestamp($unixTimestampStartDate);
                    $entryTime = $carbonStartDate->format('H:i');

                    $unixTimestampEndDate = ($excelEndDate - 25569) * 86400;
                    $carbonEndDate = Carbon::createFromTimestamp($unixTimestampEndDate);
                    $exitTime = $carbonEndDate->format('H:i');

                    $this->data[] = [
                        'student_id' => $row['student_id'],
                        'roll_no' => $row['roll_no'],
                        'entry_time' => $entryTime,
                        'exit_time' => $exitTime,
                    ];
                }
            }
        }
    }
}

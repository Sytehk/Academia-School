<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Academic\Models\Teacher;
use Modules\Authentication\Models\User;

class TeacherBulkImportService
{
    private const DEFAULT_PASSWORD = '123456';

    public function __construct(
        private readonly UserService $userService,
        private readonly TeacherService $teacherService
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{imported: int, skipped: int, failed: int, details: array<int, array{row: int, status: string, reason: string}>}
     */
    public function import(array $rows, ?int $departmentId, string $designation): array
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $summary = [
            'imported' => 0,
            'skipped' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $normalized = $this->normalizeRow($row);

            if ($this->isBlankRow($normalized)) {
                continue;
            }

            $validation = $this->validateRow($normalized);
            if ($validation->fails()) {
                $summary['failed']++;
                $summary['details'][] = [
                    'row' => $rowNumber,
                    'status' => 'failed',
                    'reason' => implode(' ', $validation->errors()->all()),
                ];

                continue;
            }

            $duplicateReason = $this->findDuplicateReason($normalized, $instituteId);
            if ($duplicateReason) {
                $summary['skipped']++;
                $summary['details'][] = [
                    'row' => $rowNumber,
                    'status' => 'skipped',
                    'reason' => $duplicateReason,
                ];

                continue;
            }

            try {
                DB::transaction(function () use ($normalized, $departmentId, $designation, $instituteId, $branchId) {
                    $this->importRow($normalized, $departmentId, $designation, $instituteId, $branchId);
                });

                $summary['imported']++;
                $summary['details'][] = [
                    'row' => $rowNumber,
                    'status' => 'imported',
                    'reason' => 'OK',
                ];
            } catch (Exception $e) {
                $summary['failed']++;
                $summary['details'][] = [
                    'row' => $rowNumber,
                    'status' => 'failed',
                    'reason' => $e->getMessage(),
                ];
                Log::error('Teacher bulk import row failed: '.$e->getMessage(), ['row' => $rowNumber, 'data' => $normalized]);
            }
        }

        return $summary;
    }

    private function normalizeRow(array $row): array
    {
        $trimmed = [];
        foreach ($row as $key => $value) {
            $trimmed[$key] = is_string($value) ? trim($value) : $value;
        }

        $trimmed['name'] = $trimmed['name'] ?? '';
        $trimmed['phone'] = preg_replace('/[^0-9+\-\s()]/', '', (string) ($trimmed['phone'] ?? ''));
        $trimmed['email'] = ! empty($trimmed['email']) ? strtolower($trimmed['email']) : null;
        $trimmed['gender'] = ! empty($trimmed['gender']) ? ucfirst(strtolower($trimmed['gender'])) : null;

        return $trimmed;
    }

    private function isBlankRow(array $row): bool
    {
        foreach ($row as $value) {
            if (! is_null($value) && $value !== '') {
                return false;
            }
        }

        return true;
    }

    private function validateRow(array $row): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($row, [
            'name' => 'required|string|max:50',
            'phone' => 'required|string|regex:/^[0-9+\-\s()]{7,20}$/',
            'email' => 'nullable|email|max:100',
            'gender' => 'nullable|in:Male,Female,Other',
        ], [
            'name.required' => 'Name is required.',
            'phone.required' => 'Phone is required.',
            'phone.regex' => 'Phone is not a valid phone number.',
            'email.email' => 'Email is not a valid email address.',
            'gender.in' => 'Gender must be Male, Female or Other.',
        ]);
    }

    private function findDuplicateReason(array $row, int $instituteId): ?string
    {
        if (User::where('institute_id', $instituteId)->where('phone', $row['phone'])->exists()) {
            return 'Duplicate: a user with this phone already exists.';
        }

        if (! empty($row['email']) && User::where('institute_id', $instituteId)->where('email', $row['email'])->exists()) {
            return 'Duplicate: a user with this email already exists.';
        }

        if (Teacher::where('institute_id', $instituteId)->where('phone', $row['phone'])->exists()) {
            return 'Duplicate: a teacher with this phone already exists.';
        }

        return null;
    }

    private function importRow(array $row, ?int $departmentId, string $designation, int $instituteId, int $branchId): void
    {
        $password = ! empty($row['password']) ? (string) $row['password'] : self::DEFAULT_PASSWORD;

        $user = $this->userService->createOrUpdateUser([
            'institute_id' => $instituteId,
            'branch_id' => $branchId,
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'password' => $password,
            'status' => 1,
            'user_type' => 'Teacher',
        ]);

        if (! $user) {
            throw new Exception('Teacher login account could not be created.');
        }

        $teacher = $this->teacherService->createTeacher([
            'user_id' => $user->id,
            'department_id' => $departmentId,
            'designation' => $designation,
            'name' => $row['name'],
            'sl' => $row['roll_no'] !== '' ? $row['roll_no'] : null,
            'gender' => $row['gender'],
            'religion' => $row['religion'] ?? null,
            'phone' => $row['phone'],
            'access_key' => $password,
        ]);

        if (! $teacher) {
            throw new Exception('Teacher record could not be created.');
        }
    }
}

<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Academic\Models\LibraryMember;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\User;

class StudentBulkImportService
{
    private const DEFAULT_PASSWORD = '123456';

    public function __construct(
        private readonly UserService $userService,
        private readonly StudentService $studentService,
        private readonly StudentSessionService $studentSessionService
    ) {}

    /**
     * Import a batch of pre-parsed spreadsheet rows for a single class/section/group/session.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{imported: int, skipped: int, failed: int, details: array<int, array{row: int, status: string, reason: string}>}
     */
    public function import(array $rows, int $classId, int $sectionId, int $groupId, int $sessionId): array
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
            $rowNumber = $index + 2; // account for the header row
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

            $duplicateReason = $this->findDuplicateReason($normalized, $instituteId, $sessionId, $classId, $sectionId);
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
                DB::transaction(function () use ($normalized, $classId, $sectionId, $groupId, $sessionId, $instituteId, $branchId) {
                    $this->importRow($normalized, $classId, $sectionId, $groupId, $sessionId, $instituteId, $branchId);
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
                Log::error('Student bulk import row failed: '.$e->getMessage(), ['row' => $rowNumber, 'data' => $normalized]);
            }
        }

        return $summary;
    }

    /**
     * Trim every value and normalize the handful of fields we rely on for validation/dedup.
     */
    private function normalizeRow(array $row): array
    {
        $trimmed = [];
        foreach ($row as $key => $value) {
            $trimmed[$key] = is_string($value) ? trim($value) : $value;
        }

        $trimmed['name'] = $trimmed['name'] ?? '';
        $trimmed['roll_no'] = isset($trimmed['roll_no']) ? preg_replace('/\D/', '', (string) $trimmed['roll_no']) : '';
        $trimmed['phone'] = $trimmed['phone'] ?? ($trimmed['mobile_no'] ?? '');
        $trimmed['phone'] = preg_replace('/[^0-9+\-\s()]/', '', (string) $trimmed['phone']);
        $trimmed['email'] = ! empty($trimmed['email']) ? strtolower($trimmed['email']) : null;
        $trimmed['registration_no'] = ! empty($trimmed['registration_no']) ? (string) $trimmed['registration_no'] : null;
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
            'roll_no' => 'required|numeric|min:1',
            'phone' => 'required|string|regex:/^[0-9+\-\s()]{7,20}$/',
            'email' => 'nullable|email|max:100',
            'gender' => 'nullable|in:Male,Female,Other',
            'registration_no' => 'nullable|string|max:50',
        ], [
            'name.required' => 'Name is required.',
            'roll_no.required' => 'Roll No is required.',
            'roll_no.numeric' => 'Roll No must be numeric.',
            'phone.required' => 'Phone/Mobile No is required.',
            'phone.regex' => 'Phone/Mobile No is not a valid phone number.',
            'email.email' => 'Email is not a valid email address.',
            'gender.in' => 'Gender must be Male, Female or Other.',
        ]);
    }

    private function findDuplicateReason(array $row, int $instituteId, int $sessionId, int $classId, int $sectionId): ?string
    {
        if (User::where('institute_id', $instituteId)->where('phone', $row['phone'])->exists()) {
            return 'Duplicate: a user with this phone already exists.';
        }

        if (! empty($row['email']) && User::where('institute_id', $instituteId)->where('email', $row['email'])->exists()) {
            return 'Duplicate: a user with this email already exists.';
        }

        if (Student::where('institute_id', $instituteId)->where('phone', $row['phone'])->exists()) {
            return 'Duplicate: a student with this phone already exists.';
        }

        if (! empty($row['registration_no']) && Student::where('institute_id', $instituteId)->where('registration_no', $row['registration_no'])->exists()) {
            return 'Duplicate: registration number already exists.';
        }

        if (DB::table('student_sessions')
            ->where('institute_id', $instituteId)
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('roll', $row['roll_no'])
            ->whereNull('deleted_at')
            ->exists()) {
            return 'Duplicate: roll already assigned in this class/section/session.';
        }

        return null;
    }

    private function importRow(array $row, int $classId, int $sectionId, int $groupId, int $sessionId, int $instituteId, int $branchId): void
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
            'user_type' => 'Student',
        ]);

        if (! $user) {
            throw new Exception('Student login account could not be created.');
        }

        $student = $this->studentService->createStudent([
            'institute_id' => $instituteId,
            'branch_id' => $branchId,
            'user_id' => $user->id,
            'first_name' => $row['name'],
            'gender' => $row['gender'],
            'religion' => $row['religion'] ?? null,
            'father_name' => $row['fathers_name'] ?? null,
            'mother_name' => $row['mothers_name'] ?? null,
            'phone' => $row['phone'],
            'registration_no' => $row['registration_no'],
            'address' => $row['address'] ?? null,
            'access_key' => $password,
            'information_sent_to_name' => $row['guardian_name'] ?? null,
            'information_sent_to_phone' => $row['guardian_number'] ?? null,
        ], $user->id);

        if (! $student) {
            throw new Exception('Student record could not be created.');
        }

        $studentSession = $this->studentSessionService->createStudentSession([
            'session_id' => $sessionId,
            'student_id' => $student->id,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'group_id' => $groupId,
            'roll' => (int) $row['roll_no'],
            'qr_code' => $this->studentSessionService->generateUniqueQrCode(),
        ]);

        if (! $studentSession) {
            throw new Exception('Student session record could not be created.');
        }

        LibraryMember::create([
            'institute_id' => $instituteId,
            'branch_id' => $branchId,
            'user_id' => $user->id,
            'member_type' => 'Student',
            'library_id' => (int) $row['roll_no'],
            'student_id' => $student->id,
        ]);
    }
}

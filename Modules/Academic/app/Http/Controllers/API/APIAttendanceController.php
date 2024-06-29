<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Enums\UserLogAction;
use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use App\Services\NotificationDispatchService;
use App\Services\QrAttendanceService;
use App\Services\SmsService;
use App\Traits\Trackable;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Academic\Models\AbsentFine;
use Modules\Academic\Models\StaffAttendance;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentAttendance;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Services\StudentService;
use Modules\Academic\Services\UserService;
use Modules\Authentication\Models\User;

class APIAttendanceController extends Controller
{
    use Trackable;

    public function __construct(
        private readonly StudentService $studentService,
        private readonly UserService $userService,
        private readonly SmsService $smsService,
        private readonly AcademicContextService $academicContext,
        private readonly NotificationDispatchService $notificationDispatch,
        private readonly QrAttendanceService $qrAttendance,
    ) {}

    public function getStudentAttendance(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $classId = (int) $request->class_id;
        $sectionId = (int) $request->section_id;
        $this->academicContext->assertClassSectionGroup($classId, $sectionId);

        $period_id = 1;

        $students = Student::select(
            'students.id as student_id',
            DB::raw("CONCAT(students.first_name, ' ', students.last_name) AS name"),
            'student_sessions.roll as roll_no',
            DB::raw('
            CASE
                WHEN students.information_sent_to_phone IS NOT NULL
                THEN students.information_sent_to_phone
                ELSE students.phone
            END AS phone
        '),
            'student_attendances.attendance',
            'student_attendances.period_id',
            'student_attendances.date'
        )
            ->join('student_sessions', 'student_sessions.student_id', '=', 'students.id')
            ->leftJoin('student_attendances', function ($join) use ($request, $period_id, $classId, $sectionId) {
                $join->on('student_attendances.student_id', '=', 'students.id')
                    ->where('student_attendances.date', $request->date)
                    ->where('student_attendances.class_id', $classId)
                    ->where('student_attendances.section_id', $sectionId)
                    ->where('student_attendances.period_id', $period_id);
            })
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->where('student_sessions.class_id', $classId)
            ->where('student_sessions.section_id', $sectionId)
            ->orderBy('student_sessions.roll')
            ->get();

        return $this->responseSuccess(
            $students,
            'Attendance students fetched successfully.'
        );
    }

    public function getStudentAbsentAttendance(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $classId = (int) $request->class_id;
        $sectionId = (int) $request->section_id;
        $date = $request->date;

        $this->academicContext->assertClassSectionGroup($classId, $sectionId);

        $students = Student::select(
            'students.id',
            'students.first_name',
            'students.last_name',
            'student_sessions.roll as roll_no',
            'students.phone',
            'students.information_sent_to_phone',
            'student_attendances.id as attendance_id',
            'student_attendances.attendance',
            'student_attendances.period_id',
            'student_attendances.date'
        )
            ->join('student_sessions', 'student_sessions.student_id', '=', 'students.id')
            ->join('student_attendances', function ($join) use ($date, $classId, $sectionId) {
                $join->on('student_attendances.student_id', '=', 'students.id')
                    ->where('student_attendances.date', $date)
                    ->where('student_attendances.class_id', $classId)
                    ->where('student_attendances.section_id', $sectionId)
                    ->where('student_attendances.attendance', 2);
            })
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->where('student_sessions.class_id', $classId)
            ->where('student_sessions.section_id', $sectionId)
            ->orderBy('student_sessions.roll', 'ASC')
            ->get()
            ->map(function ($student) {
                return [
                    'student_id' => $student->id,
                    'name' => trim($student->first_name.' '.$student->last_name),
                    'roll_no' => $student->roll_no,
                    'phone' => $student->information_sent_to_phone
                        ?: $student->phone, // ✅ SMS-ready
                    'attendance' => $student->attendance, // 2 = Absent
                    'period_id' => $student->period_id,
                    'date' => $student->date,
                ];
            });

        return $this->responseSuccess(
            $students,
            'Absent students fetched successfully.'
        );
    }

    public function sendAbsentBulkSms(Request $request): JsonResponse
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        $studentIds = $request->student_ids;

        /**
         * 🔹 Fetch students with usable phone numbers
         * Priority:
         * 1. information_sent_to_phone
         * 2. phone
         */
        $students = Student::whereIn('id', $studentIds)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where(function ($q) {
                $q->whereNotNull('information_sent_to_phone')
                    ->orWhereNotNull('phone');
            })
            ->get(['id', 'first_name', 'last_name', 'phone', 'information_sent_to_phone'])
            ->map(function ($student) {
                $phone = $student->information_sent_to_phone ?: $student->phone;

                return [
                    'id' => $student->id,
                    'name' => trim($student->first_name.' '.$student->last_name),
                    'phone' => $phone,
                ];
            })
            ->filter(fn ($s) => ! empty($s['phone']))
            ->values();

        if ($students->isEmpty()) {
            return $this->responseError([], 'No valid phone numbers found for the selected students.');
        }

        /**
         * 🔹 SMS Message
         */
        $message = "Dear Guardian,\n"
            .'This is to inform you that your child is absent today at the college. '
            ."Please ensure the reason for the absence.\n\n"
            ."Thank you,\n"
            .get_option('school_name');

        $success = [];
        $failed = [];
        foreach ($students as $student) {
            try {
                // sent_sms() catches gateway failures internally and always returns
                // an array — it never throws, so the result must be checked explicitly
                // instead of relying on this try/catch (which was previously dead code).
                $response = sent_sms($student['phone'], $message);

                if (! empty($response['success'])) {
                    $success[] = [
                        'student_id' => $student['id'],
                        'phone' => $student['phone'],
                    ];
                } else {
                    $failed[] = [
                        'student_id' => $student['id'],
                        'phone' => $student['phone'],
                        'error' => $response['error'] ?? 'Unknown error',
                    ];
                }
            } catch (\Throwable $e) {
                $failed[] = [
                    'student_id' => $student['id'],
                    'phone' => $student['phone'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $this->responseSuccess(
            [
                'total_selected' => count($studentIds),
                'valid_numbers' => $students->count(),
                'sent_count' => count($success),
                'failed_count' => count($failed),
                'failed' => $failed,
            ],
            count($failed)
                ? 'Bulk SMS sent with some failures.'
                : 'Bulk SMS sent successfully.'
        );
    }

    public function studentAttendance(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'period_id' => 'nullable|exists:periods,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date' => 'required|date',
            'attendance' => 'nullable|array|min:1',
            'attendance.*' => 'in:1,2,3', // Assuming 1=Present, 2=Absent, 3=Late
            'sms_status' => 'nullable',
        ]);

        try {
            DB::beginTransaction();

            $this->academicContext->assertClassSectionGroup(
                (int) $request->class_id,
                (int) $request->section_id,
            );
            $this->academicContext->assertStudentsInClassSection(
                $request->student_ids,
                (int) $request->class_id,
                (int) $request->section_id,
            );

            $institute_id = get_institute_id();
            $branch_id = get_branch_id();
            $date = $request->date;

            $lastAttendanceRecord = null;

            foreach ($request->student_ids as $key => $student_id) {
                $attendanceStatus = $request->attendance[$key] ?? 2; // Default to Absent (2)
                $lastAttendanceRecord = StudentAttendance::updateOrCreate(
                    [
                        'student_id' => $student_id,
                        'class_id' => $request->class_id,
                        'section_id' => $request->section_id,
                        'period_id' => $request->period_id ?? 1,
                        'date' => $date,
                    ],
                    [
                        'subject_id' => $request->subject_id,
                        'institute_id' => $institute_id,
                        'branch_id' => $branch_id,
                        'attendance' => $attendanceStatus,
                    ]
                );

                // Check if SMS should be sent for absent students.
                // A notification failure here must never roll back the attendance rows
                // already saved above, so it's isolated in its own try/catch.
                $channel = $this->notificationDispatch->resolveChannel($request->notify_channel, $request->sms_status);
                if ($channel !== NotificationDispatchService::CHANNEL_NONE && $attendanceStatus == 2) {
                    try {
                        $student = $this->studentService->getStudentById($student_id);
                        $mobile_number = $this->notificationDispatch->studentPhone($student);
                        if ($student && $mobile_number) {
                            $body = "Dear Guardian,\nThis is to inform you that your child is absent today. Please contact the school administration for any clarification.\n\nThank you,\n".get_option('school_name');

                            if ($this->notificationDispatch->shouldSendSms($channel)) {
                                $this->smsService->queueLog(
                                    $mobile_number,
                                    $body,
                                    'Student',
                                    Auth::user()->id,
                                    ['student_id' => $student->id]
                                );
                            }

                            if ($this->notificationDispatch->shouldSendWhatsApp($channel)) {
                                $this->notificationDispatch->queueWhatsApp(
                                    $mobile_number,
                                    'absent',
                                    [
                                        'student_name' => trim(($student->first_name ?? '').' '.($student->last_name ?? '')),
                                        'student_id' => $student->id,
                                        'date' => $date,
                                        'status' => 'Absent',
                                        'school_name' => get_option('school_name'),
                                        'contact' => get_option('phone'),
                                    ],
                                    $student->id,
                                    'attendance',
                                    $student->id
                                );
                            }
                        }
                    } catch (Exception $e) {
                        Log::error('Student attendance notification failed: '.$e->getMessage(), ['student_id' => $student_id, 'exception' => $e]);
                    }
                }
            }

            // Log attendance action
            $this->trackAction(
                UserLogAction::CREATE,
                StudentAttendance::class,
                Auth::id(),
                "Today's attendance recorded on $date."
            );

            DB::commit();

            return $this->responseSuccess($lastAttendanceRecord, 'Attendance recorded successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function staffAttendance(Request $request): JsonResponse
    {
        // Step 1: Base Validation
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'type' => 'required|string',
            'sms_status' => 'required|integer|in:0,1',

            'attendance' => 'required|array|min:1',

            'attendance.*.user_id' => 'required|exists:users,id',
            'attendance.*.attendance' => 'required|integer|in:1,2,3', // 1=Present, 2=Absent, 3=Late

            'attendance.*.start_time' => 'required|date_format:H:i',
            'attendance.*.end_time' => 'required|date_format:H:i',
        ]);

        // Step 2: Custom Time Validation (Server Safe)
        $validator->after(function ($validator) use ($request) {

            foreach ($request->attendance as $index => $row) {

                if (isset($row['start_time'], $row['end_time'])) {

                    $start = strtotime($request->date.' '.$row['start_time']);
                    $end = strtotime($request->date.' '.$row['end_time']);

                    // Night shift support (Optional)
                    if ($end <= $start) {
                        $end += 86400; // add 24 hours
                    }

                    if ($end <= $start) {
                        $validator->errors()->add(
                            "attendance.$index.end_time",
                            'End time must be after start time.'
                        );
                    }
                }
            }
        });

        // Step 3: Validation Error Response
        if ($validator->fails()) {
            return $this->responseError($validator->errors(), 'Validation failed', 422);
        }

        DB::beginTransaction();

        try {

            $date = $request->date;
            $type = $request->type;
            $sms_status = (int) $request->sms_status;
            $institute_id = get_institute_id();
            $branch_id = get_branch_id();

            foreach ($request->attendance as $record) {

                $staffAtt = StaffAttendance::updateOrCreate(
                    [
                        'user_id' => $record['user_id'],
                        'date' => $date,
                    ],
                    [
                        'start_time' => $record['start_time'],
                        'end_time' => $record['end_time'],
                        'attendance' => $record['attendance'],
                        'type' => $type,
                        'institute_id' => $institute_id,
                        'branch_id' => $branch_id,
                    ]
                );

                // Send SMS if Absent
                if ($sms_status === 1 && $staffAtt->attendance == 2) {
                    $this->sendSmsNotification($staffAtt);
                }
            }

            // Log Activity
            $this->trackAction(
                UserLogAction::CREATE,
                StaffAttendance::class,
                Auth::id(),
                "Staff attendance recorded on {$date}"
            );

            DB::commit();

            return $this->responseSuccess([], 'Attendance recorded successfully.');
        } catch (Exception $e) {

            DB::rollBack();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function studentAttendanceDelete(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'date' => 'required|date',
            'period_id' => 'required|integer|exists:periods,id',
            'type' => 'required|string|in:delete,fetch',
        ]);

        if ($validator->fails()) {
            return $this->responseError($validator->errors(), 'Validation failed.', 422);
        }

        $class_id = (int) $request->class_id;
        $section_id = (int) $request->section_id;
        $date = $request->date;
        $period_id = (int) $request->period_id;
        $type = $request->type;

        $this->academicContext->assertClassSectionGroup($class_id, $section_id);

        if ($type === 'delete') {
            DB::beginTransaction();
            try {
                $attendanceRecords = StudentAttendance::where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->where('date', $date)
                    ->where('period_id', $period_id)
                    ->get();

                if ($attendanceRecords->isEmpty()) {
                    return $this->responseError([], 'Attendance data not found.', 404);
                }

                // Delete attendance records
                StudentAttendance::where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->where('date', $date)
                    ->where('period_id', $period_id)
                    ->delete();

                // Log the delete action
                $this->trackAction(
                    UserLogAction::DELETE,
                    StudentAttendance::class,
                    Auth::id(),
                    "Deleted attendance records for Class ID: $class_id, Section ID: $section_id on $date, Period ID: $period_id."
                );

                DB::commit();

                return $this->responseSuccess([], 'Attendance data deleted successfully.');
            } catch (Exception $e) {
                DB::rollback();

                return $this->responseError([], 'An unexpected error occurred: '.$e->getMessage());
            }
        } else { // Fetch attendance data
            $attendanceRecords = StudentAttendance::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('date', $date)
                ->where('period_id', $period_id)
                ->get();

            return $this->responseSuccess($attendanceRecords, 'Attendance data fetched successfully.');
        }
    }

    /**
     * Admin/staff QR scanner. Shares the SAME check-in/check-out state
     * machine as the Teacher QR flow (App\Services\QrAttendanceService) — no
     * independent exists()/create() attendance logic here anymore, so a
     * student scanned once via Admin and once via Teacher (in either order)
     * still lands on one consistent row. No expected class/section is passed,
     * preserving Admin's original unrestricted-scan behavior.
     */
    public function studentQrCodeAttendance(Request $request): JsonResponse
    {
        $qrCode = $request->qr_code;
        if (! $qrCode) {
            return $this->responseError([], 'QR Code not found.', 404);
        }

        $result = $this->qrAttendance->scan($qrCode);

        return match ($result['status']) {
            QrAttendanceService::STATUS_CHECKED_IN,
            QrAttendanceService::STATUS_CHECKED_OUT,
            QrAttendanceService::STATUS_ALREADY_CHECKED_OUT => $this->responseSuccess($this->scanResponseData($result), $result['message']),
            QrAttendanceService::STATUS_NOT_FOUND => $this->responseError([], $result['message'], 404),
            QrAttendanceService::STATUS_INACTIVE, QrAttendanceService::STATUS_SECTION_MISMATCH => $this->responseError([], $result['message'], 422),
            default => $this->responseError([], 'Something went wrong.', 500),
        };
    }

    /**
     * {status: check_in|check_out|already_checked_out, check_in_at, check_out_at}
     * — same shape as the Teacher QR controllers, so Admin/Teacher responses
     * are consistent for the same underlying state.
     */
    private function scanResponseData(array $result): array
    {
        return [
            'status' => match ($result['status']) {
                QrAttendanceService::STATUS_CHECKED_IN => 'check_in',
                QrAttendanceService::STATUS_CHECKED_OUT => 'check_out',
                QrAttendanceService::STATUS_ALREADY_CHECKED_OUT => 'already_checked_out',
                default => $result['status'],
            },
            'check_in_at' => $result['check_in_at'],
            'check_out_at' => $result['check_out_at'],
        ];
    }

    public function studentAttendanceReport(Request $request): JsonResponse
    {
        // Validate incoming data
        $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'student_id' => 'nullable|integer|exists:students,id', // New filter
        ]);

        $from_date = $request->from_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $to_date = $request->to_date ?? date('Y-m-d');
        $percentage = (int) $request->percentage ?? 0;

        $class_id = (int) $request->class_id;
        $section_id = (int) $request->section_id;
        $filterStudentId = (int) $request->student_id;

        $this->academicContext->assertClassSectionGroup($class_id, $section_id);

        $studentsQuery = StudentSession::with(['student'])
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->when(get_option('academic_year'), fn ($q) => $q->where('session_id', get_option('academic_year')))
            ->where('class_id', $class_id)
            ->where('section_id', $section_id);

        if ($filterStudentId) {
            $studentsQuery->where('student_id', $filterStudentId);
        }

        $students = $studentsQuery->orderBy('student_sessions.roll', 'asc')->get();

        // Fetch attendance data
        $attendanceData = DB::table('student_attendances')
            ->where('student_attendances.institute_id', get_institute_id())
            ->where('student_attendances.branch_id', get_branch_id())
            ->where('student_attendances.class_id', $class_id)
            ->where('student_attendances.section_id', $section_id)
            ->whereBetween('date', [$from_date, $to_date])
            ->whereIn('student_attendances.student_id', $students->pluck('student_id'))
            ->get();

        // Prepare report data
        $report_data = [];
        foreach ($students as $studentSession) {
            $studentId = $studentSession->student_id;
            $attendance = $attendanceData->where('student_id', $studentId);
            $presentCount = $attendance->where('attendance', 1)->count();
            $absentCount = $attendance->where('attendance', 2)->count();
            $totalClasses = $presentCount + $absentCount;
            $attendanceRatio = $totalClasses > 0 ? ($presentCount * 100 / $totalClasses) : 0;

            if ($percentage > 0 && $attendanceRatio < $percentage) {
                continue;
            }

            $report_data[] = [
                'student_id' => $studentId,
                'student_name' => $studentSession->student?->first_name.' '.$studentSession->student?->last_name,
                'present' => $presentCount,
                'absent' => $absentCount,
                'attendance_ratio' => floor($attendanceRatio),
            ];
        }

        return $this->responseSuccess($report_data, 'Attendance report generated successfully.');
    }

    public function absentFineReport(Request $request): JsonResponse
    {
        try {
            // Validate the request parameters
            $request->validate([
                'class_id' => 'required|integer',
                'section_id' => 'required|integer',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date',
            ]);

            $class_id = intval($request->class_id);
            $section_id = intval($request->section_id);
            $from_date = $request->from_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $to_date = $request->to_date ?? date('Y-m-d');

            $this->academicContext->assertClassSectionGroup($class_id, $section_id);
            $sessionId = get_option('academic_year');

            // If no filters are applied, return an empty response
            if (empty($request->all())) {
                return response()->json([
                    'status' => true,
                    'message' => 'No data provided for report.',
                    'data' => [],
                ]);
            }

            $class = get_class_name($class_id);
            $section = get_section_name($section_id);

            // Get the processed data from the database
            $processedData = DB::table('students')
                ->join('student_attendances', 'students.id', '=', 'student_attendances.student_id')
                ->join('student_sessions', function ($join) use ($class_id, $section_id, $sessionId) {
                    $join->on('student_sessions.student_id', '=', 'students.id')
                        ->where('student_sessions.class_id', $class_id)
                        ->where('student_sessions.section_id', $section_id)
                        ->when($sessionId, fn ($q) => $q->where('student_sessions.session_id', $sessionId));
                })
                ->select(
                    'students.id as student_id',
                    'students.first_name',
                    'students.last_name',
                    'student_sessions.roll',
                    'student_sessions.section_id',
                    'student_attendances.period_id'
                )
                ->where('student_attendances.institute_id', get_institute_id())
                ->where('student_attendances.branch_id', get_branch_id())
                ->where('student_attendances.class_id', $class_id)
                ->where('student_attendances.section_id', $section_id)
                ->where('student_attendances.attendance', 2)
                ->whereBetween('student_attendances.date', [$from_date, $to_date])
                ->whereIn('student_attendances.period_id', [1, 2, 3])
                ->orderBy('student_sessions.roll', 'ASC')
                ->get();

            // Calculate fines based on processed data
            $fines = $processedData->groupBy('roll')->map(function ($studentData) {
                $attendanceAbsentFine = AbsentFine::where('period_id', 1)
                    ->where('institute_id', get_institute_id())
                    ->where('branch_id', get_branch_id())
                    ->first();

                $quizAbsentFine = AbsentFine::where('period_id', 2)
                    ->where('institute_id', get_institute_id())
                    ->where('branch_id', get_branch_id())
                    ->first();

                $labAbsentFine = AbsentFine::where('period_id', 3)
                    ->where('institute_id', get_institute_id())
                    ->where('branch_id', get_branch_id())
                    ->first();

                // Calculate fines for each period
                $attendanceFine = $studentData->where('period_id', 1)->count() * floatval($attendanceAbsentFine->fee_amount ?? 0);
                $quizFine = $studentData->where('period_id', 2)->count() * floatval($quizAbsentFine->fee_amount ?? 0);
                $labFine = $studentData->where('period_id', 3)->count() * floatval($labAbsentFine->fee_amount ?? 0);

                $totalFine = $attendanceFine + $quizFine + $labFine;

                return [
                    'student_id' => $studentData->first()->student_id,
                    'roll' => $studentData->first()->roll,
                    'first_name' => $studentData->first()->first_name,
                    'last_name' => $studentData->first()->last_name,
                    'labFine' => $labFine,
                    'quizFine' => $quizFine,
                    'attendanceFine' => $attendanceFine,
                    'totalFine' => $totalFine,
                ];
            });

            // Calculate total fine amount
            $totalFineAmount = $fines->sum('totalFine');

            // Return JSON response with the report data
            return response()->json([
                'status' => true,
                'message' => 'Absent fine report generated successfully.',
                'data' => [
                    'class' => $class,
                    'section' => $section,
                    'from_date' => $from_date,
                    'to_date' => $to_date,
                    'fines' => $fines->values(),
                    'totalFineAmount' => $totalFineAmount,
                ],
            ]);
        } catch (Exception $e) {
            // Return an error response in case of failure
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while generating the report.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function studentAttendanceReportStatus(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'status' => 'required|in:1,2', // 1 = Present, 2 = Absent
            'period_id' => 'nullable|integer', // Optional override for period
        ]);

        $class_id = $request->class_id;
        $section_id = $request->section_id;
        $status = $request->status;
        $period_id = $request->period_id ?? 1;

        $this->academicContext->assertClassSectionGroup((int) $class_id, (int) $section_id);

        $attendanceCounts = [];

        DB::table('student_attendances')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('attendance', $status)
            ->where('period_id', $period_id)
            ->orderBy('id')
            ->chunk(100, function ($chunkedData) use (&$attendanceCounts) {
                foreach ($chunkedData as $attendance) {
                    $day = (int) date('d', strtotime($attendance->date));
                    $month = (int) date('m', strtotime($attendance->date));

                    $key = $month.'-'.$day;

                    if (! isset($attendanceCounts[$key])) {
                        $attendanceCounts[$key] = [
                            'month' => $month,
                            'day' => $day,
                            'P' => 0,
                            'A' => 0,
                        ];
                    }

                    if ($attendance->attendance == 1) {
                        $attendanceCounts[$key]['P']++;
                    } elseif ($attendance->attendance == 2) {
                        $attendanceCounts[$key]['A']++;
                    }
                }
            });

        // Convert associative array to indexed array
        $responseData = array_values($attendanceCounts);

        return $this->responseSuccess($responseData, 'Student attendance status report generated.');
    }

    public function studentAttendanceMonthlyReportView(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'period_id' => 'required|integer|exists:periods,id',
            'month' => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'], // Fix here
        ]);

        $class_id = (int) $request->class_id;
        $section_id = (int) $request->section_id;
        $period_id = (int) $request->period_id;

        $this->academicContext->assertClassSectionGroup($class_id, $section_id);

        [$monthNumber, $year] = explode('/', $request->month);
        $num_of_days = cal_days_in_month(CAL_GREGORIAN, (int) $monthNumber, (int) $year);
        $sessionId = get_option('academic_year');

        $students = DB::table('students')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->when($sessionId, fn ($q) => $q->where('student_sessions.session_id', $sessionId))
            ->where('student_sessions.class_id', $class_id)
            ->where('student_sessions.section_id', $section_id)
            ->where('students.status', '1')
            ->select('students.id as student_id', 'students.first_name', 'students.last_name', 'students.phone', 'student_sessions.roll')
            ->orderBy('student_sessions.roll', 'asc')
            ->get();

        if ($students->isEmpty()) {
            return $this->responseSuccess([], 'No students found for the given class and section.');
        }

        $studentIds = $students->pluck('student_id');

        // Get attendance records for the month
        $attendances = DB::table('student_attendances')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->whereIn('student_id', $studentIds)
            ->whereMonth('date', $monthNumber)
            ->whereYear('date', $year)
            ->where('period_id', $period_id)
            ->get();

        // Attendance codes
        $attendance_value = [
            '0' => '',
            '1' => 'P',  // Present
            '2' => 'A',  // Absent
            '3' => 'L',  // Leave
            '4' => 'H',  // Holiday
            '5' => 'EL',  // Exam Leave or similar
        ];

        // Build structured data
        $report = [];

        foreach ($students as $student) {
            $studentReport = [
                'student_id' => $student->student_id,
                'student_name' => $student->first_name.' '.$student->last_name,
                'student_phone' => $student->phone,
                'roll' => $student->roll,
                'attendance' => [],
            ];

            for ($i = 1; $i <= $num_of_days; $i++) {
                $date = sprintf('%04d-%02d-%02d', $year, $monthNumber, $i);
                $attendanceRecord = $attendances->first(fn ($a) => $a->student_id == $student->student_id && $a->date == $date);
                $code = $attendanceRecord ? $attendance_value[$attendanceRecord->attendance] : '';
                $studentReport['attendance'][] = [
                    'date' => $date,
                    'status' => $code,
                ];
            }

            $report[] = $studentReport;
        }

        return $this->responseSuccess([
            'class_id' => $class_id,
            'section_id' => $section_id,
            'period_id' => $period_id,
            'month' => $request->month,
            'num_of_days' => $num_of_days,
            'report' => $report,
        ], 'Monthly student attendance report generated successfully.');
    }

    public function staffAttendanceReport(Request $request): JsonResponse
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);

        try {
            $from_date = $request->from_date;
            $to_date = $request->to_date;

            $institute_id = get_institute_id();
            $branch_id = get_branch_id();

            $attendances = DB::table('staff_attendances')
                ->where('institute_id', $institute_id)
                ->where('branch_id', $branch_id)
                ->whereBetween('date', [$from_date, $to_date])
                ->orderBy('date')
                ->get();

            $userIds = $attendances->pluck('user_id')->unique();

            $users = User::whereIn('id', $userIds)->get()->keyBy('id');

            $reportData = [];

            foreach ($attendances as $row) {

                $user = $users[$row->user_id] ?? null;

                if (! $user) {
                    continue;
                }

                if (! isset($reportData[$row->user_id])) {

                    $reportData[$row->user_id] = [
                        'user_id' => $row->user_id,
                        'name' => $user->name,
                        'role' => $row->type,
                        'present' => 0,
                        'absent' => 0,
                        'late' => 0,
                        'on_leave' => 0,
                        'logs' => [],
                    ];
                }

                // 1=Present, 2=Absent, 3=Late — the only values ever written by the save
                // endpoints. 4/5 (Holiday/Exam Leave) are reserved codes not currently
                // written anywhere; only those genuinely-unrecognized codes count as
                // on_leave — Late was previously mislabeled into that bucket.
                if ($row->attendance == 1) {
                    $reportData[$row->user_id]['present']++;
                } elseif ($row->attendance == 2) {
                    $reportData[$row->user_id]['absent']++;
                } elseif ($row->attendance == 3) {
                    $reportData[$row->user_id]['late']++;
                } else {
                    $reportData[$row->user_id]['on_leave']++;
                }

                $reportData[$row->user_id]['logs'][] = [
                    'date' => $row->date,
                    'attendance' => $row->attendance,
                    'start_time' => $row->start_time,
                    'end_time' => $row->end_time,
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Staff attendance report generated successfully.',
                'data' => array_values($reportData),
            ]);
        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while generating the report.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function sendSmsNotification($staffAtt): bool
    {
        try {
            $teacher = $this->userService->findUserById((int) $staffAtt->user_id);

            if (! $teacher) {
                return false;
            }

            // Need Principal Number. env() outside config/ returns null once config
            // is cached in production, and this key was never set anywhere anyway —
            // use the same per-institute contact number the WEB flow already relies on.
            $mobile_number = get_option('phone');

            if (empty($mobile_number)) {
                return false;
            }

            $body = "Dear Principal,
                This Teacher marked as absent today.
                Name: {$teacher->name}
                ID: {$teacher->id}
                I request your cooperation in this matter.
                Thank you,
                Academic Office";

            $this->smsService->queueLog(
                $mobile_number,
                $body,
                $staffAtt->type,
                Auth::user()->id,
                ['teacher_id' => intval($teacher->id)]
            );

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function deleteAttendanceData($class_id, $section_id, $date, $period_id): bool
    {
        try {
            DB::beginTransaction();
            $deletedRows = StudentAttendance::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('date', $date)
                ->where('period_id', $period_id)
                ->delete();

            $deletedRows > 0;
            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            return false;
        }
    }
}

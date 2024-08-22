<?php

namespace Modules\Finance\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\AbsentFine;
use Modules\Finance\Models\AttendanceWaiver;
use Modules\Finance\Models\StudentWaiverConfig;

class WaiverConfigController extends Controller
{
    public function index(): JsonResponse
    {
        $classId = (int) request()->class_id;
        $sectionId = (int) request()->section_id;

        $waiverLists = StudentWaiverConfig::query()
            ->join('students', 'student_waiver_configs.student_id', '=', 'students.id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->leftJoin('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->join('fee_heads', 'student_waiver_configs.fee_head_id', '=', 'fee_heads.id')
            ->join('waivers', 'student_waiver_configs.waiver_id', '=', 'waivers.id')

            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())

            // Filters
            ->when($classId, function ($q) use ($classId) {
                $q->where('student_sessions.class_id', $classId);
            })
            ->when($sectionId, function ($q) use ($sectionId) {
                $q->where('student_sessions.section_id', $sectionId);
            })

            ->select([
                'student_waiver_configs.id as waiver_config_id',
                'student_waiver_configs.amount as waiver_amount',

                'students.id as student_id',
                'students.first_name',
                'students.last_name',

                'users.email',
                'users.phone',

                'student_sessions.roll',

                'classes.class_name',
                'sections.section_name',

                'fee_heads.id as fee_head_id',
                'fee_heads.name as fee_head_name',

                'waivers.id as waiver_id',
                'waivers.waiver as waiver_name',
            ])

            ->orderBy('student_sessions.roll', 'ASC')
            ->get();

        return $this->responseSuccess(
            [
                'waiver_list' => $waiverLists,
            ],
            _lang('Waiver configuration list fetched successfully.')
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'students' => 'required|array|min:1',
            'students.*' => 'required|exists:students,id',
            'fee_head_id' => 'nullable|exists:fee_heads,id',
            'wavier_id' => 'required|exists:waivers,id',
            'waiver_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $students = $request->students;
            $feeHeadId = $request->fee_head_id;
            $waiverId = $request->wavier_id;
            $waiverAmount = $request->waiver_amount;

            foreach ($students as $studentId) {

                StudentWaiverConfig::updateOrCreate(
                    [
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'student_id' => $studentId,
                        'fee_head_id' => $feeHeadId,
                        'waiver_id' => $waiverId,
                    ],
                    [
                        'amount' => $waiverAmount,
                    ]
                );
            }

            DB::commit();

            return $this->responseSuccess(
                [],
                _lang('Waiver Config created or updated successfully.')
            );
        } catch (Exception $e) {

            DB::rollBack();

            return $this->responseError(
                [],
                $e->getMessage()
            );
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $studentWaiverConfig = StudentWaiverConfig::where('id', $id)->first();

        if (! empty($studentWaiverConfig)) {
            $studentWaiverConfig->delete();

            return $this->responseSuccess(
                [],
                _lang('Waiver Config has been delete successfully.')
            );
        } else {
            return $this->responseError([], _lang('Something went wrong. Waiver Config can not be delete.'));
        }
    }

    public function attendanceWaiver()
    {
        $classId = (int) request()->class_id;
        $sectionId = (int) request()->section_id;

        $processedData = DB::table('students')
            ->join('student_attendances', 'students.id', '=', 'student_attendances.student_id')
            ->join('student_sessions', 'student_sessions.student_id', '=', 'students.id')
            ->select(
                'students.id as student_id',
                'students.first_name',
                'student_sessions.roll',
                'student_sessions.section_id',
                'student_attendances.period_id'
            )
            ->where('student_attendances.class_id', $classId)
            ->where('student_attendances.section_id', $sectionId)
            ->where('student_attendances.attendance', 2)
            ->whereIn('student_attendances.period_id', [1, 2, 3])
            ->orderBy('student_sessions.roll', 'ASC')
            ->get();

        $fines = $processedData->groupBy('roll')->map(function ($studentData) {
            $attendanceAbsentFine = AbsentFine::where('period_id', 1)->first();
            $quizAbsentFine = AbsentFine::where('period_id', 2)->first();
            $labAbsentFine = AbsentFine::where('period_id', 3)->first();

            $attendanceFine = $studentData->where('period_id', 1)->count() * floatval($attendanceAbsentFine->fee_amount);
            $quizFine = $studentData->where('period_id', 2)->count() * floatval($quizAbsentFine->fee_amount);
            $labFine = $studentData->where('period_id', 3)->count() * floatval($labAbsentFine->fee_amount);

            $totalFine = $attendanceFine + $quizFine + $labFine;

            return [
                'student_id' => $studentData->first()->student_id,
                'roll' => $studentData->first()->roll,
                'first_name' => $studentData->first()->first_name,
                'labFine' => $labFine,
                'quizFine' => $quizFine,
                'attendanceFine' => $attendanceFine,
                'totalFine' => $totalFine,
            ];
        });

        $totalFineAmount = $fines->sum('totalFine');

        return $this->responseSuccess([
            'fines' => $fines->values(), // Ensuring it's formatted correctly
            'classId' => $classId,
            'sectionId' => $sectionId,
            'totalFineAmount' => $fines->sum('totalFine'),
        ], _lang('Waiver Config has been deleted successfully.'));
    }

    public function attendanceWaiverStore(Request $request): JsonResponse
    {
        // Validate the incoming request
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'attendance_fines' => 'required|array|min:1',
            'quiz_fines' => 'required|array|min:1',
            'lab_fines' => 'required|array|min:1',

            'student_ids.*' => 'required|exists:students,id',
            'attendance_fines.*' => 'required|numeric|min:0',
            'quiz_fines.*' => 'required|numeric|min:0',
            'lab_fines.*' => 'required|numeric|min:0',
        ]);

        // Ensure all arrays have the same length
        $studentIds = $request->student_ids;
        $attendanceFines = $request->attendance_fines;
        $quizFines = $request->quiz_fines;
        $labFines = $request->lab_fines;

        if (count($studentIds) !== count($attendanceFines) || count($studentIds) !== count($quizFines) || count($studentIds) !== count($labFines)) {
            return $this->responseError(_lang('All input arrays must have the same number of elements.'), 422);
        }

        // Loop through the student IDs
        for ($i = 0; $i < count($studentIds); $i++) {
            // Find existing AttendanceWaiver or create a new instance
            $attendanceWaiver = AttendanceWaiver::firstOrNew(['student_id' => $studentIds[$i]]);

            // Update the fields
            $attendanceWaiver->attendance_fine = $attendanceFines[$i];
            $attendanceWaiver->quiz_fine = $quizFines[$i];
            $attendanceWaiver->lab_fine = $labFines[$i];
            $attendanceWaiver->total_waiver = $attendanceWaiver->attendance_fine + $attendanceWaiver->quiz_fine + $attendanceWaiver->lab_fine;

            // Save the attendance waiver
            $attendanceWaiver->save();
        }

        return $this->responseSuccess([], _lang('Attendance Waiver Successfully.'));
    }
}

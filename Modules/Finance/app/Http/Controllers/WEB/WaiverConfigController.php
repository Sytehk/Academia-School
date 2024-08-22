<?php

namespace Modules\Finance\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\AbsentFine;
use Modules\Academic\Models\Student;
use Modules\Finance\Models\AttendanceWaiver;
use Modules\Finance\Models\StudentWaiverConfig;
use RealRashid\SweetAlert\Facades\Alert;

class WaiverConfigController extends Controller
{
    public function index(): View|Factory
    {
        $sectionId = (int) request()->section_id;
        $groupId = (int) request()->group;
        $studentCategoryId = (int) request()->student_category_id;

        $students = Student::query()
            ->select('users.*', 'student_sessions.roll', 'classes.class_name', 'sections.section_name', 'students.id as id', 'student_groups.group_name', 'students.student_category_id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->leftJoin('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->leftJoin('student_groups', 'student_sessions.group_id', '=', 'student_groups.id')
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->where('users.user_type', 'Student')
            ->where('student_sessions.section_id', $sectionId)
            ->where('student_sessions.group_id', $groupId)
            ->where('students.student_category_id', $studentCategoryId)
            ->orderBy('student_sessions.roll', 'ASC')
            ->get();

        $waiverConfigLists = StudentWaiverConfig::join('students', 'student_waiver_configs.student_id', '=', 'students.id')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->join('fee_heads', 'student_waiver_configs.fee_head_id', '=', 'fee_heads.id')
            ->join('waivers', 'student_waiver_configs.waiver_id', '=', 'waivers.id')
            ->select('student_waiver_configs.id', 'student_waiver_configs.amount', 'student_waiver_configs.student_id', 'students.first_name', 'students.last_name', 'student_sessions.roll', 'fee_heads.name', 'waivers.waiver')
            ->get();

        return view('finance::waiver_config.index', [
            'students' => $students,
            'waiverConfigLists' => $waiverConfigLists,
        ]);
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'students' => 'required|array',
            'fee_head_id' => 'nullable',
            'wavier_id' => 'required',
            'waiver_amount' => 'required',
        ]);

        $students = $request->input('students');
        $fee_head_id = $request->input('fee_head_id');
        $wavier_id = $request->input('wavier_id');
        $waiver_amount = $request->input('waiver_amount');
        DB::beginTransaction();
        foreach ($students as $student) {
            $studenWaiverConfig = new StudentWaiverConfig;
            $studenWaiverConfig->student_id = $student;
            $studenWaiverConfig->fee_head_id = $fee_head_id;
            $studenWaiverConfig->waiver_id = $wavier_id;
            $studenWaiverConfig->amount = $waiver_amount;
            $studenWaiverConfig->save();
        }
        DB::commit();

        Alert::toast('Waiver Config has been create successfully.', 'success');

        return redirect('waiver-config');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $studentWaiverConfig = StudentWaiverConfig::where('id', $id)->first();

        if (! empty($studentWaiverConfig)) {
            $studentWaiverConfig->delete();

            Alert::toast('Waiver Config has been delete successfully.', 'success');

            return redirect('waiver-config');
        } else {
            Alert::error('Error', _lang('Something went wrong. Waiver Config can not be delete.'));

            return redirect('waiver-config');
        }
    }

    public function attendanceWaiver(): Factory|View
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
            $quizAbsentFine = AbsentFine::where('period_id', 6)->first();
            $labAbsentFine = AbsentFine::where('period_id', 7)->first();

            $attendanceFine = $studentData->where('period_id', 1)->count() * floatval($attendanceAbsentFine->fee_amount);
            $quizFine = $studentData->where('period_id', 6)->count() * floatval($quizAbsentFine->fee_amount);
            $labFine = $studentData->where('period_id', 7)->count() * floatval($labAbsentFine->fee_amount);

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

        return view('finance::waiver_config.attendance_waiver', [
            'fines' => $fines,
            'classId' => $classId,
            'sectionId' => $sectionId,
            'totalFineAmount' => $totalFineAmount,
        ]);
    }

    public function attendanceWaiverStore(Request $request): Redirector|RedirectResponse
    {
        // Validate the incoming request
        $request->validate([
            'student_ids' => 'required|array',
            'attendance_fines' => 'required|array',
            'quiz_fines' => 'required|array',
            'lab_fines' => 'required|array',
            'student_ids.*' => 'required|exists:students,id',
            'attendance_fines.*' => 'required|min:0',
            'quiz_fines.*' => 'required|min:0',
            'lab_fines.*' => 'required|min:0',
        ]);

        // Ensure all arrays have the same length
        $studentIds = $request->student_ids;
        $attendanceFines = $request->attendance_fines;
        $quizFines = $request->quiz_fines;
        $labFines = $request->lab_fines;

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

        Alert::toast(_lang('Attendance Waiver Successfully.'), 'success');

        return redirect('attendance-waiver');
    }
}

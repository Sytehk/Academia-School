<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Academic\Models\ExamAttendance;
use Modules\Academic\Models\ExamSchedule;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\Subject;
use RealRashid\SweetAlert\Facades\Alert;

class ExamAttendanceController extends Controller
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
    ) {}

    public function exam_schedule(Request $request, $type = 'view'): Factory|View
    {
        $exam = $request->input('exam');
        $class = $request->input('class');
        $group = $request->input('group');

        if ($exam != '' && $class != '') {
            $subjects = Subject::select('*', 'exam_schedules.id as schedules_id', 'subjects.id as subject_id')
                ->leftJoin('exam_schedules', function ($join) use ($exam) {
                    $join->on('subjects.id', '=', 'exam_schedules.subject_id');
                    $join->where('exam_schedules.exam_id', $exam);
                })
                ->where('subjects.class_id', $class)
                ->get();

            return view('academic::exam.schedule.create', compact('subjects', 'class', 'group', 'exam', 'type'));
        } else {
            return view('academic::exam.schedule.create', compact('class', 'group', 'exam', 'type'));
        }
    }

    public function exam_attendance(Request $request): Factory|View
    {
        $examId = $request->input('exam_id');
        $class_id = $request->input('class_id');
        $section_id = $request->input('section_id');
        $subject_id = $request->input('subject_id');
        $examDate = null;

        if ($examId && $class_id && $section_id && $subject_id) {

            // The actual scheduled exam date, not "today" — attendance must be keyed
            // to the real exam date so re-opening this screen on a later day still
            // finds (and updates) the same rows instead of inserting duplicates.
            $examDate = ExamSchedule::where('exam_id', $examId)
                ->where('class_id', $class_id)
                ->where('subject_id', $subject_id)
                ->value('date') ?? date('Y-m-d');

            $attendance = Student::select(
                'students.*',
                'student_sessions.class_id',
                'student_sessions.section_id',
                'student_sessions.roll',
                'exam_attendances.id AS attendance_id',
                'exam_attendances.attendance'
            )
                ->leftJoin('exam_attendances', function ($join) use ($examId, $subject_id, $section_id, $examDate) {

                    $join->on('exam_attendances.student_id', '=', 'students.id')
                        ->where('exam_attendances.exam_id', $examId)
                        ->where('exam_attendances.subject_id', $subject_id)
                        ->where('exam_attendances.section_id', $section_id)
                        ->whereDate('exam_attendances.date', $examDate);
                })
                ->join('student_sessions', 'student_sessions.student_id', '=', 'students.id')
                ->where('student_sessions.institute_id', get_institute_id())
                ->where('student_sessions.branch_id', get_branch_id())
                ->where('student_sessions.class_id', $class_id)
                ->where('student_sessions.section_id', $section_id)
                ->where('students.status', '1')
                ->orderBy('student_sessions.roll', 'ASC')
                ->get();

            return view(
                'academic::exam.attendance.attendance',
                compact(
                    'attendance',
                    'class_id',
                    'section_id',
                    'examId',
                    'subject_id',
                    'examDate'
                )
            );
        }

        return view(
            'academic::exam.attendance.attendance',
            compact(
                'class_id',
                'section_id',
                'examId',
                'subject_id',
                'examDate'
            )
        );
    }

    public function store_exam_attendance(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|integer|exists:exams,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'in:1,2',
        ]);

        $this->academicContext->assertClassSectionGroup(
            (int) $request->class_id,
            (int) $request->section_id,
        );
        $this->academicContext->assertStudentsInClassSection(
            array_keys($request->attendance),
            (int) $request->class_id,
            (int) $request->section_id,
        );

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->attendance as $student_id => $status) {
                    ExamAttendance::updateOrCreate(
                        [
                            'institute_id' => get_institute_id(),
                            'branch_id' => get_branch_id(),
                            'exam_id' => $request->exam_id,
                            'subject_id' => $request->subject_id,
                            'class_id' => $request->class_id,
                            'section_id' => $request->section_id,
                            'student_id' => $student_id,
                            'date' => $request->date,
                        ],
                        [
                            'attendance' => $status,
                        ]
                    );
                }
            });

            Alert::toast('Exam Attendance take successfully.', 'success');
        } catch (Exception $e) {
            Log::error('Exam attendance save failed: '.$e->getMessage(), ['exception' => $e]);
            Alert::error('Error', $e->getMessage());
        }

        return redirect('exams-attendance');
    }
}

<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Enums\UserLogAction;
use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use App\Services\NotificationDispatchService;
use App\Services\SmsService;
use App\Traits\Trackable;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Academic\Http\Requests\StudentAttendanceCreateRequest;
use Modules\Academic\Models\AbsentFine;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Period;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\StaffAttendance;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentAttendance;
use Modules\Academic\Models\Subject;
use Modules\Academic\Repositories\SectionRepository;
use Modules\Academic\Services\PeriodService;
use Modules\Academic\Services\SubjectService;
use Modules\Academic\Services\UserService;
use Modules\Authentication\Models\User;
use PDF;
use RealRashid\SweetAlert\Facades\Alert;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly SectionRepository $sectionRepo,
        private readonly PeriodService $periodService,
        private readonly UserService $userService,
        private readonly SubjectService $subjectService,
        private readonly SmsService $smsService,
        private readonly AcademicContextService $academicContext,
        private readonly NotificationDispatchService $notificationDispatch,
    ) {}

    use Trackable;

    public function student_attendance(Request $request): View|RedirectResponse
    {
        $attendance = [];

        $class_id = (int) $request->class_id;
        $section_id = (int) $request->section_id;
        $date = $request->date;
        $period_id = $request->period_id; // ✅ optional
        $subject_id = $request->subject_id; // ✅ optional

        $subjects = $this->subjectService->getSubjectsByClassId($class_id);
        $sections = $this->sectionRepo->getSectionsByClassId($class_id);
        $periods = $this->periodService->getPeriods();

        // ✅ Only required fields
        if (! $class_id || ! $section_id || ! $date) {
            return view('academic::attendance.student-attendance', compact(
                'attendance',
                'date',
                'class_id',
                'section_id',
                'subjects',
                'sections',
                'periods',
                'period_id',
                'subject_id'
            ));
        }

        try {
            $this->academicContext->assertClassSectionGroup($class_id, $section_id);

            $class = ClassModel::findOrFail($class_id)->class_name;
            $section = Section::findOrFail($section_id)->section_name;

            $periodName = null;
            if ($period_id) {
                $periodName = Period::find($period_id)?->period;
            }

            $subjectName = null;
            if ($subject_id) {
                $subjectName = Subject::find($subject_id)?->subject_name;
            }

            $attendancePeriodId = $period_id ?: 1;

            $attendance = Student::select(
                'students.*',
                'student_attendances.id as attendance_id',
                'student_attendances.attendance',
                'student_sessions.qr_code',
                'student_sessions.roll as roll_no'
            )
                ->leftJoin('student_attendances', function ($join) use ($date, $class_id, $section_id, $attendancePeriodId) {
                    $join->on('student_attendances.student_id', '=', 'students.id')
                        ->where('student_attendances.date', $date)
                        ->where('student_attendances.class_id', $class_id)
                        ->where('student_attendances.section_id', $section_id)
                        ->where('student_attendances.period_id', $attendancePeriodId);
                })
                ->join('student_sessions', 'student_sessions.student_id', '=', 'students.id')
                ->where('student_sessions.session_id', get_option('academic_year'))
                ->where('student_sessions.institute_id', get_institute_id())
                ->where('student_sessions.branch_id', get_branch_id())
                ->where('student_sessions.class_id', $class_id)
                ->where('student_sessions.section_id', $section_id)
                ->where('students.status', '=', '1')
                ->orderBy('student_sessions.roll', 'ASC')
                ->get();

            return view('academic::attendance.student-attendance', compact(
                'attendance',
                'date',
                'class',
                'section',
                'class_id',
                'section_id',
                'subject_id',   // ✅ important
                'subjectName',   // ✅ optional display
                'subjects',
                'sections',
                'periods',
                'period_id',   // ✅ important
                'periodName'   // ✅ optional display
            ));
        } catch (Exception $e) {
            return back()->with('error', 'Invalid Class or Section');
        }
    }

    public function student_attendance_save(StudentAttendanceCreateRequest $request): Redirector|RedirectResponse
    {
        try {
            DB::beginTransaction();

            app(AcademicContextService::class)->assertClassSectionGroup(
                (int) $request->class_id,
                (int) $request->section_id,
            );

            $this->academicContext->assertStudentsInClassSection(
                $request->student_ids,
                (int) $request->class_id,
                (int) $request->section_id,
            );

            $className = ClassModel::findOrFail($request->class_id)->class_name;
            $sectionName = Section::findOrFail($request->section_id)->section_name;
            $periodId = (int) ($request->period_id ?? 1);

            foreach ($request->student_ids as $i => $student_id) {
                if (! $student_id) {
                    continue;
                } // skip nulls

                $studentAtt = StudentAttendance::firstOrNew([
                    'student_id' => (int) $student_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'period_id' => $periodId,
                    'date' => $request->date,
                ]);

                $wasAbsentBefore = $studentAtt->exists && (int) $studentAtt->attendance === 2;

                $studentAtt->institute_id = get_institute_id();
                $studentAtt->branch_id = get_branch_id();
                $studentAtt->subject_id = $request->subject_id;
                $studentAtt->attendance = $request->attendance[$i] ?? 2;
                $studentAtt->save();

                $channel = $this->notificationDispatch->resolveChannel($request->notify_channel, $request->sms_status);
                if ($channel !== NotificationDispatchService::CHANNEL_NONE && (int) $studentAtt->attendance === 2 && ! $wasAbsentBefore) {

                    try {
                        $student = Student::find($student_id);
                        $mobileNumber = $this->notificationDispatch->studentPhone($student);

                        if ($student && $mobileNumber) {
                            $roll = DB::table('student_sessions')
                                ->where('student_id', $student->id)
                                ->where('session_id', get_option('academic_year'))
                                ->where('class_id', $request->class_id)
                                ->where('section_id', $request->section_id)
                                ->value('roll');

                            $parameters = [
                                'student_name' => trim($student->first_name.' '.$student->last_name),
                                'student_id' => $student->id,
                                'class' => $className,
                                'section' => $sectionName,
                                'roll' => $roll,
                                'date' => $request->date,
                                'status' => 'Absent',
                                'school_name' => get_option('school_name'),
                                'contact' => get_option('phone'),
                            ];

                            if ($this->notificationDispatch->shouldSendSms($channel)) {
                                $this->notificationDispatch->queueSms(
                                    $mobileNumber,
                                    "Dear Guardian, {$parameters['student_name']} (Roll: {$parameters['roll']}) of {$parameters['class']}-{$parameters['section']} was absent on {$parameters['date']}. {$parameters['school_name']}",
                                    'Student',
                                    (int) (Auth::id() ?: 0),
                                    ['student_id' => $student->id]
                                );
                            }

                            if ($this->notificationDispatch->shouldSendWhatsApp($channel)) {
                                $this->notificationDispatch->queueWhatsApp(
                                    $mobileNumber,
                                    'absent',
                                    $parameters,
                                    $student->id,
                                    'attendance',
                                    $studentAtt->id
                                );
                            }
                        }
                    } catch (Exception $e) {
                        Log::error('Student attendance notification failed: '.$e->getMessage(), ['student_id' => $student_id, 'exception' => $e]);
                    }
                }
            }

            // Track action
            $this->trackAction(
                UserLogAction::CREATE,
                StudentAttendance::class,
                Auth::id(),
                "Today's attendance taken for user ID: ".Auth::id().' on date: '.$request->date
            );

            DB::commit();

            Alert::toast('Attendance taken successfully!', 'success');

            return redirect('/student-attendance');
        } catch (Exception $e) {
            DB::rollback();
            Alert::error('Error', $e->getMessage());

            return redirect('/student-attendance');
        }
    }

    public function staff_attendance(Request $request): Factory|View
    {
        $attendance = [];
        $date = $request->date ?? now()->format('Y-m-d');
        $user_type = $request->user_type;

        // ✅ Get roles dynamically (exclude owner, superadmin)
        $roleList = DB::table('roles')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->orderBy('name', 'ASC')
            ->get();

        if (! $user_type) {
            return view('academic::attendance.staff-attendance', compact('attendance', 'date', 'user_type', 'roleList'));
        }

        // A bare '*' here (as it was) expands to every column from BOTH users and
        // staff_attendances. institute_id/branch_id/created_at/updated_at exist on
        // both tables, and since this is a LEFT JOIN, a staff member with no
        // attendance row yet for $date has NULLs on the staff_attendances side --
        // those NULLs would win the collision (last column processed) and
        // silently overwrite the real users.institute_id/branch_id with NULL for
        // exactly the staff who haven't been marked yet. 'users.*' is qualified
        // (takes only users' own columns, unambiguous) plus the specific
        // staff_attendances columns the view needs -- none of which exist on
        // users, so there is no longer any collision to depend on.
        $attendance = User::select(
            'users.*',
            'users.id as user_id',
            'staff_attendances.id as attendance_id',
            'staff_attendances.attendance',
            'staff_attendances.start_time',
            'staff_attendances.end_time',
        )
            ->leftJoin('staff_attendances', function ($join) use ($date) {
                $join->on('users.id', '=', 'staff_attendances.user_id')
                    ->where('staff_attendances.date', '=', $date);
            })
            ->where('users.institute_id', get_institute_id())
            ->where('users.branch_id', get_branch_id())
            ->where('user_type', $user_type)
            ->orderBy('users.id', 'DESC')
            ->get();

        return view('academic::attendance.staff-attendance', compact('attendance', 'date', 'user_type', 'roleList'));
    }

    public function staff_attendance_save(Request $request)
    {
        $request->validate([
            'user_id' => 'required|array',
            'date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $data = [];
            $now = now();

            foreach ($request->user_id as $i => $userId) {

                $attendanceStatus = $request->attendance[$i] ?? 2; // default absent

                $data[] = [
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'user_id' => $userId,
                    'date' => $request->date,
                    'start_time' => $request->start_time[$i] ?? '09:00',
                    'end_time' => $request->end_time[$i] ?? '17:00',
                    'attendance' => $attendanceStatus,
                    'type' => $request->type,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // ✅ BULK UPSERT (Insert OR Update if exists)
            StaffAttendance::upsert(
                $data,
                ['user_id', 'date'], // unique keys
                ['start_time', 'end_time', 'attendance', 'type', 'updated_at']
            );

            // ✅ SMS Logic (only for absent users)
            // A notification failure here must never roll back the attendance rows already
            // upserted above — isolate it in its own try/catch, matching student_attendance_save().
            $channel = $this->notificationDispatch->resolveChannel($request->notify_channel, $request->sms_status);
            if ($channel !== NotificationDispatchService::CHANNEL_NONE) {
                try {
                    $absentUsers = collect($data)->where('attendance', 2);

                    // Every id is already in hand from the upsert payload above; a
                    // single whereIn() replaces what was one findUserById() query
                    // per absent staff member.
                    $teachersById = User::whereIn('id', $absentUsers->pluck('user_id')->all())
                        ->get()
                        ->keyBy('id');

                    foreach ($absentUsers as $item) {
                        $teacher = $teachersById->get((int) $item['user_id']);

                        if (! $teacher) {
                            continue;
                        }
                        $mobile_number = $teacher->phone ?: get_option('phone');
                        $body = "Dear Principal,\nThis Teacher marked as absent today.\nName: {$teacher->name}\nID: {$teacher->id}";

                        if ($this->notificationDispatch->shouldSendSms($channel)) {
                            $this->smsService->queueLog(
                                $mobile_number,
                                $body,
                                $request->type,
                                getUserId(),
                                ['teacher_id' => $teacher->id]
                            );
                        }

                        if ($this->notificationDispatch->shouldSendWhatsApp($channel)) {
                            $this->notificationDispatch->queueWhatsApp(
                                $mobile_number,
                                'absent',
                                [
                                    'student_name' => $teacher->name,
                                    'student_id' => $teacher->id,
                                    'class' => '',
                                    'section' => '',
                                    'roll' => '',
                                    'date' => $request->date,
                                    'status' => 'Absent',
                                    'school_name' => get_option('school_name'),
                                    'contact' => get_option('phone'),
                                ],
                                null,
                                'staff_attendance',
                                $teacher->id
                            );
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Staff attendance notification failed: '.$e->getMessage(), ['exception' => $e]);
                }
            }

            DB::commit();

            Alert::toast('Attendance saved successfully!', 'success');

            return redirect('/staffs-attendance');
        } catch (Exception $e) {
            DB::rollback();

            Alert::error('Error', $e->getMessage());

            return redirect('/staffs-attendance');
        }
    }

    public function studentAttendanceDelete(Request $request): Factory|Redirector|RedirectResponse|View
    {
        $periods = $this->periodService->getPeriods();
        $class_id = (int) $request->input('class_id');
        $section_id = (int) $request->input('section_id');
        $date = $request->input('date');
        $period_id = (int) $request->input('period_id');
        $type = $request->input('type');
        $result = null;
        $sections = $this->sectionRepo->getSectionsByClassId($class_id);

        if ($request->isMethod('post') && $type == 'delete') {
            $this->academicContext->assertClassSectionGroup($class_id, $section_id);

            $result = $this->deleteAttendanceData($class_id, $section_id, $date, $period_id);
            if ($result === true) {
                Alert::toast('Attendance data deleted successfully.', 'success');

                redirect('student_attendance/delete');
            } elseif ($result === false) {
                Alert::error('Error', 'Attendance data not found.');

                return redirect('/student_attendance/delete');
            } else {
                Alert::error('Error', 'An unexpected error occurred.');

                return redirect('/student_attendance/delete');
            }
        } else {
            if ($class_id && $section_id) {
                $this->academicContext->assertClassSectionGroup($class_id, $section_id);
            }

            $result = StudentAttendance::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('date', $date)
                ->where('period_id', $period_id)
                ->get();
        }

        return view('academic::attendance.student_attendance_delete', compact('class_id', 'section_id', 'date', 'period_id', 'periods', 'result', 'sections'));
    }

    private function deleteAttendanceData($class_id, $section_id, $date, $period_id): Redirector|RedirectResponse
    {
        try {
            DB::beginTransaction();
            $deletedRows = StudentAttendance::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('date', $date)
                ->where('period_id', $period_id)
                ->delete();

            $success = $deletedRows > 0;
            DB::commit();

            Alert::toast('Student attendance delete successfully.', 'success');

            return redirect('/student-attendance');
        } catch (Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('/student-attendance');
        }
    }

    public function student_attendance_date_to_date(Request $request, $view = ''): Factory|View
    {
        $class_id = null;
        $section_id = null;
        $from_date = $request->from_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $to_date = $request->to_date ?? date('Y-m-d');
        $percentage = floatval($request->percentage);

        if ($view == '') {
            return view('academic::attendance.reports.student_attendance_ratio_date_to_date', compact(
                'class_id',
                'section_id',
                'from_date',
                'to_date',
                'percentage'
            ));
        } else {
            $class_id = $request->class_id;
            $section_id = $request->section_id;

            if ($class_id && $section_id) {
                $this->academicContext->assertClassSectionGroup((int) $class_id, (int) $section_id);
            }

            $class = get_class_name($class_id);
            $section = get_section_name($section_id);
            $sessionId = get_option('academic_year');

            $query2 = DB::table('students')
                ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
                ->where('student_sessions.institute_id', get_institute_id())
                ->where('student_sessions.branch_id', get_branch_id())
                ->when($sessionId, fn ($q) => $q->where('student_sessions.session_id', $sessionId))
                ->where('student_sessions.class_id', $class_id)
                ->where('student_sessions.section_id', $section_id)
                ->where('students.status', '1')
                ->whereNull('students.deleted_at')
                ->orderBy('student_sessions.roll', 'asc')
                ->get();

            $students = [];
            foreach ($query2 as $student) {
                $students[$student->student_id] = $student;
            }

            // One grouped aggregate instead of two COUNT queries per student
            // (the previous 2N-query pattern that scaled with class size).
            $report_data = $this->buildAttendanceRatioData($query2, $class_id, $section_id, $from_date, $to_date);

            return view('academic::attendance.reports.student_attendance_ratio_date_to_date', compact(
                'class_id',
                'section_id',
                'class',
                'section',
                'report_data',
                'students',
                'from_date',
                'to_date',
                'percentage'
            ));
        }
    }

    public function absentFineReport(Request $request): Factory|View
    {
        $class_id = intval($request->class_id);
        $section_id = intval($request->section_id);
        $type = $request->type;
        $from_date = $request->from_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $to_date = $request->to_date ?? date('Y-m-d');

        if ($request->all() == []) {
            return view('academic::attendance.reports.absent_fine_search_report', compact('class_id', 'section_id', 'from_date', 'to_date'));
        } else {
            $this->academicContext->assertClassSectionGroup($class_id, $section_id);

            $class = get_class_name($class_id);
            $section = get_section_name($section_id);
            $sessionId = get_option('academic_year');

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

            // Fetched once for the whole report instead of once per student/roll group.
            $attendanceAbsentFine = AbsentFine::where('period_id', 1)->first();
            $quizAbsentFine = AbsentFine::where('period_id', 2)->first();
            $labAbsentFine = AbsentFine::where('period_id', 3)->first();

            $fines = $processedData->groupBy('roll')->map(function ($studentData) use ($attendanceAbsentFine, $quizAbsentFine, $labAbsentFine) {

                $attendanceFine = $attendanceAbsentFine
                    ? $studentData->where('period_id', 1)->count() * floatval($attendanceAbsentFine->fee_amount)
                    : 0;

                $quizFine = $quizAbsentFine
                    ? $studentData->where('period_id', 2)->count() * floatval($quizAbsentFine->fee_amount)
                    : 0;

                $labFine = $labAbsentFine
                    ? $studentData->where('period_id', 3)->count() * floatval($labAbsentFine->fee_amount)
                    : 0;

                $totalFine = $attendanceFine + $quizFine + $labFine;

                return [
                    'roll' => $studentData->first()->roll,
                    'first_name' => $studentData->first()->first_name,
                    'attendanceFine' => $attendanceFine,
                    'quizFine' => $quizFine,
                    'labFine' => $labFine,
                    'totalFine' => $totalFine,
                ];
            });

            $totalFineAmount = $fines->sum('totalFine');

            return view('academic::attendance.reports.absent_fine_search_report', compact('class_id', 'section_id', 'from_date', 'to_date', 'class', 'section', 'processedData', 'fines', 'totalFineAmount'));
        }
    }

    public function studentAttendanceRatioDateToDatePdfDownload(Request $request, $view = '')
    {
        $logo = base64_encode(file_get_contents(public_path('/'.get_logo_file_path())));
        $class_id = $request->class_id;
        $section_id = $request->section_id;

        $this->academicContext->assertClassSectionGroup((int) $class_id, (int) $section_id);

        $class = get_class_name($class_id);
        $section = get_section_name($section_id);
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $percentage = $request->percentage ?? 0;
        $sessionId = get_option('academic_year');

        $query2 = DB::table('students')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->when($sessionId, fn ($q) => $q->where('student_sessions.session_id', $sessionId))
            ->where('student_sessions.class_id', $class_id)
            ->where('student_sessions.section_id', $section_id)
            ->where('students.status', '1')
            ->whereNull('students.deleted_at')
            ->orderBy('student_sessions.roll', 'asc')
            ->get();

        $students = [];
        foreach ($query2 as $student) {
            $students[$student->student_id] = $student;
        }

        // Shared single-query aggregation (was a 2N-query loop identical to the
        // on-screen report). Keeps the PDF and HTML reports byte-for-byte consistent.
        $report_data = $this->buildAttendanceRatioData($query2, $class_id, $section_id, $from_date, $to_date);

        $pdf = PDF::loadView('academic::attendance.reports.student_attendance_ratio_date_to_date', compact(
            'logo',
            'class_id',
            'section_id',
            'class',
            'section',
            'report_data',
            'from_date',
            'students',
            'to_date',
            'percentage'
        ))->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
        ])
            ->setPaper([0, 0, 841.89, 1190], 'landscape');

        // Format dates for the filename
        $formatted_from_date = date('Y-m-d', strtotime($from_date));
        $formatted_to_date = date('Y-m-d', strtotime($to_date));
        // Concatenate dates into the filename
        $filename = 'Student_Attendance_Ratio_Report_'.$formatted_from_date.'_to_'.$formatted_to_date.'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Build the present/absent/ratio matrix for a class-section over a date range
     * using ONE grouped query, replacing the previous pattern of two COUNT queries
     * per student (which scaled linearly with class size — 120+ queries for 60
     * students). The returned shape is identical to the old per-student array so
     * the shared Blade/PDF view needs no changes.
     *
     * @param  Collection<int, object>  $students
     * @return array<int, array{present: int, absent: int, ratio: float}>
     */
    private function buildAttendanceRatioData($students, $classId, $sectionId, string $fromDate, string $toDate): array
    {
        $studentIds = collect($students)->pluck('student_id')->filter()->unique()->values()->all();

        $report_data = [];
        foreach ($studentIds as $id) {
            $report_data[$id] = ['present' => 0, 'absent' => 0, 'ratio' => 0];
        }

        if ($studentIds === []) {
            return $report_data;
        }

        $rows = DB::table('student_attendances')
            ->selectRaw('student_id, SUM(attendance = 1) as present, SUM(attendance = 2) as absent')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('period_id', 1)
            ->whereBetween('date', [$fromDate, $toDate])
            ->whereIn('student_id', $studentIds)
            ->whereNull('deleted_at')
            ->groupBy('student_id')
            ->get();

        foreach ($rows as $row) {
            $present = (int) $row->present;
            $absent = (int) $row->absent;
            $total = $present + $absent;

            $report_data[$row->student_id] = [
                'present' => $present,
                'absent' => $absent,
                'ratio' => $total > 0 ? floor($present * 100 / $total) : 0,
            ];
        }

        return $report_data;
    }
}

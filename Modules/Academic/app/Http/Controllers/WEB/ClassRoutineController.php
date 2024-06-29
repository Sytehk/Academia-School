<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\ClassRoutine;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\ExamSchedule;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Subject;
use RealRashid\SweetAlert\Facades\Alert;

class ClassRoutineController extends Controller
{
    public function index(): Factory|View
    {
        $class = '';
        $routine_list = ClassRoutine::RoutineList();

        return view('academic::class_routine.routine_list', compact('routine_list', 'class'));
    }

    public function class($class_id = ''): Factory|View
    {
        $class = sql_escape($class_id);
        $routine_list = ClassRoutine::RoutineList($class_id);

        return view('academic::class_routine.routine_list', compact('routine_list', 'class'));
    }

    public function manage($class_id = '', $section_id = ''): Factory|Redirector|RedirectResponse|View
    {
        $data = [];
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        // Retrieve class and section using the provided IDs, scoped to the acting
        // institute/branch so one tenant can't view/manage another's routine by guessing IDs.
        $data['class'] = ClassModel::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->find($class_id);
        $data['section'] = Section::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->find($section_id);

        // Check if class and section are found
        if (! $data['class'] || ! $data['section']) {
            Alert::error('Error', _lang('Class or Section not found.'));

            return redirect('class_routines');
        }

        // Retrieve the routine associated with the class and section
        $data['routine'] = ClassRoutine::getRoutine($class_id, $section_id);

        // Check if routine data is empty and handle appropriately
        if (empty($data['routine'])) {
            $data['routine'] = []; // Ensures routine is always an array
        }

        return view('academic::class_routine.routine_add', $data);
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $len = count($request->subject_id ?? []);
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        // Check every row for a teacher/room conflict *before* writing anything, so a bad
        // row doesn't get a chance to partially save and the admin gets a specific, actionable
        // error instead of a generic transaction failure.
        for ($i = 0; $i < $len; $i++) {
            $startTime = $request->start_time[$i] ?? null;
            $endTime = $request->end_time[$i] ?? null;

            if (empty($startTime)) {
                continue;
            }

            $currentId = ! empty($request->routine_id[$i]) ? (int) $request->routine_id[$i] : null;

            $conflict = ClassRoutine::findConflict(
                $request->day[$i],
                $startTime,
                $endTime,
                $request->teacher_id[$i] ?? null,
                $request->room[$i] ?? null,
                $currentId
            );

            if ($conflict) {
                $conflictType = ($request->teacher_id[$i] ?? null) == $conflict->teacher_id ? 'teacher' : 'room';
                Alert::error('Schedule Conflict', _lang("This {$conflictType} is already booked on {$request->day[$i]} between {$conflict->start_time} and {$conflict->end_time}."));

                return redirect()->back();
            }
        }

        // Start a database transaction
        DB::beginTransaction();
        try {
            $insertData = [];
            $updateData = [];

            for ($i = 0; $i < $len; $i++) {
                if (empty($request->start_time[$i])) {
                    continue;
                }

                $temp = [
                    'class_id' => $request->class_id[$i] ?? null,
                    'section_id' => $request->section_id[$i],
                    'subject_id' => $request->subject_id[$i],
                    'day' => $request->day[$i],
                    'start_time' => $request->start_time[$i],
                    'end_time' => $request->end_time[$i],
                    'teacher_id' => $request->teacher_id[$i] ?: null,
                    'room' => $request->room[$i] ?? null,
                    'updated_at' => Carbon::now(),
                ];

                if (empty($request->routine_id[$i])) {
                    $temp['institute_id'] = $instituteId;
                    $temp['branch_id'] = $branchId;
                    $temp['created_at'] = Carbon::now();
                    $insertData[] = $temp;
                } else {
                    $temp['id'] = (int) $request->routine_id[$i];
                    $updateData[] = $temp;
                }
            }

            // Insert new rows in one batch
            if (! empty($insertData)) {
                ClassRoutine::insert($insertData);
            }

            // Update existing rows individually (their own institute/branch already set)
            foreach ($updateData as $d) {
                $id = $d['id'];
                unset($d['id']);
                ClassRoutine::where('id', $id)
                    ->where('institute_id', $instituteId)
                    ->where('branch_id', $branchId)
                    ->update($d);
            }

            // Commit the transaction
            DB::commit();

            Alert::toast('Class routine save Successfully.', 'success');

            return redirect('class_routines');
        } catch (\Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect()->back();
        }
    }

    public function show($class_id = '', $section_id = ''): Factory|View
    {
        $data = [];
        $data['class'] = ClassModel::find($class_id);
        $data['section'] = Section::find($section_id);
        $data['routine'] = ClassRoutine::getRoutineView($class_id, $section_id);

        return view('academic::class_routine.routine_view', $data);
    }

    public function exam_schedule(Request $request, $type = 'view')
    {
        $exam = $request->input('exam');
        $class = $request->input('class');
        $group = $request->input('group');
        $exams = Exam::all();

        if ($exam != '' && $class != '') {
            $subjects = Subject::select('*', 'exam_schedules.id as schedules_id', 'subjects.id as subject_id')
                ->leftJoin('exam_schedules', function ($join) use ($exam) {
                    $join->on('subjects.id', '=', 'exam_schedules.subject_id');
                    $join->where('exam_schedules.exam_id', $exam);
                })
                ->where('subjects.class_id', $class)
                ->get();

            return view('academic::class_routine.exam-routine', compact('subjects', 'class', 'group', 'exam', 'type', 'exams'));
        } else {
            return view('academic::class_routine.exam-routine', compact('class', 'group', 'exam', 'type', 'exams'));
        }
    }

    public function store_exam_schedule(Request $request)
    {
        // Start a database transaction
        DB::beginTransaction();

        try {
            // Validate input
            $request->validate([
                'exam_id' => 'required|numeric',
                'class_id' => 'required|numeric',
            ]);

            $exam = $request->exam_id;
            $class = $request->class_id;

            $examSchedules = [];
            $unselectedSubjectIds = [];

            // Loop through subjects and collect valid data for insertion. Room is optional —
            // routines are not used to book physical rooms, so requiring one discarded the
            // whole subject's schedule whenever it was left blank (BUG-015).
            //
            // Only subjects whose checkbox was ticked are scheduled (FEAT-004) — an unticked
            // subject is recorded so any previously-saved schedule for it can be removed
            // below, rather than left behind from before it was deselected.
            foreach ($request->subject_ids as $subject_id => $mark_config_data) {
                if (empty($mark_config_data['selected'])) {
                    $unselectedSubjectIds[] = $subject_id;

                    continue;
                }

                if (empty($mark_config_data['date']) || empty($mark_config_data['start_time']) || empty($mark_config_data['end_time'])) {
                    continue;
                }

                $date = $mark_config_data['date'];
                $start_time = $mark_config_data['start_time'];
                $end_time = $mark_config_data['end_time'];
                // exam_schedules.room is NOT NULL — default to '' rather than null so an
                // omitted key can never throw a QueryException here.
                $room = $mark_config_data['room'] ?? '';

                // Collect the schedule data
                // institute_id/branch_id are NOT NULL FKs on exam_schedules — this
                // previously omitted them entirely, so every insert threw a
                // QueryException (the sibling APIExamScheduleController::store sets
                // them correctly).
                $examSchedules[] = [
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'session_id' => get_option('academic_year'),
                    'exam_id' => $exam,
                    'class_id' => $class,
                    'subject_id' => $subject_id,
                    'date' => $date,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'room' => $room,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            // Insert or update the exam schedule. Matching only on exam/class/subject let two
            // tenants (or two sessions) sharing those ids overwrite each other's schedule —
            // institute_id/branch_id/session_id must be part of the match key too (DB-001).
            foreach ($examSchedules as $examSchedule) {
                ExamSchedule::updateOrInsert(
                    [
                        'institute_id' => $examSchedule['institute_id'],
                        'branch_id' => $examSchedule['branch_id'],
                        'session_id' => $examSchedule['session_id'],
                        'exam_id' => $examSchedule['exam_id'],
                        'class_id' => $examSchedule['class_id'],
                        'subject_id' => $examSchedule['subject_id'],
                    ],
                    $examSchedule
                );
            }

            // A subject that was previously scheduled and is now unticked must drop out of
            // the routine, not just stay un-updated (FEAT-004).
            if (! empty($unselectedSubjectIds)) {
                ExamSchedule::where('institute_id', get_institute_id())
                    ->where('branch_id', get_branch_id())
                    ->where('session_id', get_option('academic_year'))
                    ->where('exam_id', $exam)
                    ->where('class_id', $class)
                    ->whereIn('subject_id', $unselectedSubjectIds)
                    ->delete();
            }

            // Commit the transaction
            DB::commit();

            // Success alert
            Alert::toast('Exam schedule saved successfully.', 'success');

            // Redirect to the exam routines page
            return redirect('exam-routines');
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            // Error alert
            Alert::error('Error', $e->getMessage());

            // Redirect back to the exam routines page
            return redirect('exam-routines');
        }
    }
}

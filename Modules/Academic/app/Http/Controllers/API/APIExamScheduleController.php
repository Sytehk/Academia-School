<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\ExamSchedule;
use Modules\Academic\Models\Subject;

class APIExamScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Retrieve input data from the request
        $examId = (int) $request->exam_id;
        $classId = (int) $request->class_id;

        // Fetch all exams (not currently used in the data fetch, but you can use it in the view)
        $exams = Exam::all();

        // Fetch subjects and join with exam schedules based on the selected exam ID
        $subjects = Subject::select('subjects.subject_name', 'subjects.subject_code', 'subjects.subject_type', 'subjects.class_id', 'subjects.group_id', 'exam_schedules.*', 'exam_schedules.id as schedules_id', 'subjects.id as subject_id')
            ->leftJoin('exam_schedules', function ($join) use ($examId) {
                $join->on('subjects.id', '=', 'exam_schedules.subject_id');
                $join->where('exam_schedules.exam_id', $examId);
            })
            ->where('subjects.class_id', $classId)
            ->get();

        // Merge the exams and subjects (if needed, you can customize this part)
        $mergedData = $subjects->map(function ($subject) use ($exams) {
            // Add exam data to each subject (you could customize this as needed)
            $subject->exam = $exams->where('id', $subject->exam_id)->first(); // Assuming `exam_id` exists in the subject or schedules

            return $subject;
        });

        // Return the combined data with a success message
        return $this->responseSuccess($mergedData, _lang('Data have been fetched.'));
    }

    public function store(Request $request): JsonResponse
    {
        // Start a database transaction
        DB::beginTransaction();

        try {
            // Validate input
            $request->validate([
                'exam_id' => 'required|numeric|exists:exams,id',
                'class_id' => 'required|numeric|exists:classes,id',
            ]);

            $exam = intval($request->exam_id);
            $class = intval($request->class_id);
            $examSchedules = [];

            // Loop through subjects and collect valid data for insertion
            foreach ($request->subject_ids as $mark_config_data) {
                if (empty($mark_config_data['date']) || empty($mark_config_data['start_time']) || empty($mark_config_data['end_time']) || empty($mark_config_data['room'])) {
                    continue;
                }

                $subjectId = $mark_config_data['subject_id'];
                $date = $mark_config_data['date'];
                $start_time = $mark_config_data['start_time'];
                $end_time = $mark_config_data['end_time'];
                $room = $mark_config_data['room'];

                // Collect the schedule data
                $examSchedules[] = [
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'session_id' => intval(get_option('academic_year')),
                    'exam_id' => $exam,
                    'class_id' => $class,
                    'subject_id' => $subjectId,
                    'date' => $date,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'room' => $room,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            // Insert or update the exam schedule
            foreach ($examSchedules as $examSchedule) {
                ExamSchedule::updateOrInsert(
                    [
                        'exam_id' => $examSchedule['exam_id'],
                        'class_id' => $examSchedule['class_id'],
                        'subject_id' => $examSchedule['subject_id'],
                    ],
                    $examSchedule
                );
            }

            // Commit the transaction
            DB::commit();

            return $this->responseSuccess([], _lang('Exam schedule saved successfully.'));
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        $schedule = ExamSchedule::with(['exam', 'subject', 'class'])
            ->find($id);

        if (! $schedule) {
            return $this->responseError([], _lang('Exam schedule not found.'), 404);
        }

        return $this->responseSuccess($schedule, _lang('Exam schedule fetched successfully.'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $schedule = ExamSchedule::find($id);

        if (! $schedule) {
            return $this->responseError([], _lang('Exam schedule not found.'), 404);
        }

        $validated = $request->validate([
            'exam_id' => 'required|numeric|exists:exams,id',
            'class_id' => 'required|numeric|exists:classes,id',
            'subject_id' => 'required|numeric|exists:subjects,id',
            'date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'room' => 'required|string|max:255',
        ]);

        try {
            $validated['updated_at'] = now();
            $schedule->update($validated);

            return $this->responseSuccess($schedule, _lang('Exam schedule updated successfully.'));
        } catch (\Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $schedule = ExamSchedule::find($id);

        if (! $schedule) {
            return $this->responseError([], _lang('Exam schedule not found.'), 404);
        }

        try {
            $schedule->delete();

            return $this->responseSuccess([], _lang('Exam schedule deleted successfully.'));
        } catch (\Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}

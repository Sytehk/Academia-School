<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\ClassRoutine;

class APIClassRoutineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $classId = (int) $request->class_id;
        $sectionId = (int) $request->section_id;
        $routine_list = ClassRoutine::getRoutineView($classId, $sectionId);

        return $this->responseSuccess($routine_list, _lang('Routine has been fetch.'));
    }

    public function store(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $request->validate([
            'routine' => 'required|array',
            'routine.*.class_id' => 'required|exists:classes,id',
            'routine.*.section_id' => 'required|exists:sections,id',
            'routine.*.subject_id' => 'required|exists:subjects,id',
            'routine.*.day' => 'required|string',
            'routine.*.start_time' => 'required|string',
            'routine.*.end_time' => 'required|string',
            'routine.*.teacher_id' => 'required|exists:teachers,id',
            'routine.*.room' => 'nullable|string|max:50',
        ]);

        // Reject the whole batch if any row conflicts, before writing anything.
        foreach ($request->routine as $routineData) {
            if (empty($routineData['start_time'])) {
                continue;
            }

            $existingId = null;
            $existing = ClassRoutine::where('subject_id', $routineData['subject_id'])
                ->where('class_id', $routineData['class_id'])
                ->where('section_id', $routineData['section_id'])
                ->where('day', $routineData['day'])
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->first();
            if ($existing) {
                $existingId = $existing->id;
            }

            $conflict = ClassRoutine::findConflict(
                $routineData['day'],
                $routineData['start_time'],
                $routineData['end_time'],
                $routineData['teacher_id'] ?? null,
                $routineData['room'] ?? null,
                $existingId
            );

            if ($conflict) {
                $conflictType = ($routineData['teacher_id'] ?? null) == $conflict->teacher_id ? 'teacher' : 'room';

                return $this->responseError([], _lang("Schedule conflict: this {$conflictType} is already booked on {$routineData['day']} between {$conflict->start_time} and {$conflict->end_time}."), 422);
            }
        }

        // Start a database transaction
        DB::beginTransaction();
        try {
            $dataToInsert = [];
            $dataToUpdate = [];

            foreach ($request->routine as $routineData) {
                // Check if the start time is empty
                if (empty($routineData['start_time'])) {
                    continue; // Skip if start time is empty
                }

                // Check if routine exists
                $existingRoutine = ClassRoutine::where('subject_id', $routineData['subject_id'])
                    ->where('class_id', $routineData['class_id'])
                    ->where('section_id', $routineData['section_id'])
                    ->where('day', $routineData['day'])
                    ->where('institute_id', get_institute_id())
                    ->where('branch_id', get_branch_id())
                    ->first();

                if ($existingRoutine) {
                    // If exists, prepare for update
                    $routineData['updated_at'] = Carbon::now();
                    $routineData['id'] = $existingRoutine->id; // Capture the ID for the update
                    $dataToUpdate[] = $routineData;
                } else {
                    // If does not exist, prepare for insert
                    $routineData['institute_id'] = get_institute_id();
                    $routineData['branch_id'] = get_branch_id();
                    $routineData['created_at'] = Carbon::now();
                    $routineData['updated_at'] = Carbon::now();
                    $dataToInsert[] = $routineData;
                }
            }

            // Insert new records
            if (! empty($dataToInsert)) {
                ClassRoutine::insert($dataToInsert);
            }

            // Update existing records
            foreach ($dataToUpdate as $data) {
                ClassRoutine::where('id', $data['id'])
                    ->where('institute_id', get_institute_id())
                    ->where('branch_id', get_branch_id())
                    ->update($data);
            }

            // Commit the transaction
            DB::commit();

            return $this->responseSuccess([], _lang('Class routines saved successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError([], $e->getMessage());
        }
    }

    private function findScopedRoutine(int $id): ?ClassRoutine
    {
        return ClassRoutine::where('id', $id)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->first();
    }

    public function show(int $id): JsonResponse
    {
        $routine = $this->findScopedRoutine($id);

        if (! $routine) {
            return $this->responseError([], _lang('Class routine not found.'), 404);
        }

        return $this->responseSuccess($routine, _lang('Class routine fetched successfully.'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $routine = $this->findScopedRoutine($id);

        if (! $routine) {
            return $this->responseError([], _lang('Class routine not found.'), 404);
        }

        $data = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'day' => 'required|string',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'teacher_id' => 'required|exists:teachers,id',
            'room' => 'nullable|string|max:50',
        ]);

        $conflict = ClassRoutine::findConflict(
            $data['day'],
            $data['start_time'],
            $data['end_time'],
            $data['teacher_id'] ?? null,
            $data['room'] ?? null,
            $routine->id
        );

        if ($conflict) {
            $conflictType = ($data['teacher_id'] ?? null) == $conflict->teacher_id ? 'teacher' : 'room';

            return $this->responseError([], _lang("Schedule conflict: this {$conflictType} is already booked on {$data['day']} between {$conflict->start_time} and {$conflict->end_time}."), 422);
        }

        try {
            $data['updated_at'] = now();

            $routine->update($data);

            return $this->responseSuccess($routine, _lang('Class routine updated successfully.'));
        } catch (\Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $routine = $this->findScopedRoutine($id);

        if (! $routine) {
            return $this->responseError([], _lang('Class routine not found.'), 404);
        }

        try {
            $routine->delete();

            return $this->responseSuccess([], _lang('Class routine deleted successfully.'));
        } catch (\Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}

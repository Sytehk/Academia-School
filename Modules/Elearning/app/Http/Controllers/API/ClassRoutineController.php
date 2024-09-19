<?php

namespace Modules\Elearning\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Elearning\Http\Requests\ClassRoutine\ClassRoutineStoreRequest;
use Modules\Elearning\Http\Requests\ClassRoutine\ClassRoutineUpdateRequest;
use Modules\Elearning\Models\ClassRoutine;
use Modules\Elearning\Repositories\ClassRoutineRepository;

class ClassRoutineController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private ClassRoutineRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'ClassRoutine has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(ClassRoutineStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'ClassRoutine has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'ClassRoutine has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(ClassRoutineUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'ClassRoutine has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'ClassRoutine has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function checkTeacherAvailability(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => 'required|integer|exists:users,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'days' => 'required|array|min:1',
            'days.*' => 'integer|exists:days,id',
        ]);

        $teacherId = $data['teacher_id'];
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];
        $days = $data['days'];

        // Query to check if teacher has any routine overlapping this time
        $conflict = ClassRoutine::where('teacher_id', $teacherId)
            ->whereHas('days', function ($q) use ($days) {
                $q->whereIn('days.id', $days);
            })
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'status' => false,
                'message' => 'This teacher is already assigned at the same time on selected day(s).',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Teacher is available for the selected time.',
        ]);
    }
}

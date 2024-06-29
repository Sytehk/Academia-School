<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\ClassAssign;

class ClassAssignController extends Controller
{
    public function index(): JsonResponse
    {
        $class_assigns = DB::table('class_assigns')
            ->join('teachers', 'class_assigns.teacher_id', '=', 'teachers.id')
            ->join('classes', 'class_assigns.class_id', '=', 'classes.id')
            ->select('teachers.name as teacher_name', 'classes.class_name as class_name', 'class_assigns.id as id', 'class_assigns.teacher_id', 'class_assigns.class_id')
            ->get();

        return $this->responseSuccess($class_assigns, 'Class Assigns have been fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        $teacher_id = $request->teacher_id;
        $class_id = $request->class_id;

        DB::beginTransaction();
        try {
            $exists = ClassAssign::where('teacher_id', $teacher_id)
                ->where('class_id', $class_id)
                ->exists();

            if ($exists) {
                return $this->responseError([], _lang('Shift Already Added to this teacher.'));
            } else {
                $assign_class = new ClassAssign;
                $assign_class->institute_id = get_institute_id();
                $assign_class->branch_id = get_branch_id();
                $assign_class->teacher_id = $teacher_id;
                $assign_class->class_id = $class_id;
                $assign_class->save();
                DB::commit();

                return $this->responseSuccess($assign_class, 'Shift Assign successfully');
            }
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError([], _lang('Failed to insert records unsuccessfully.'));
        }
    }

    public function show(int $id): JsonResponse
    {
        $assign_class = ClassAssign::where('id', $id)->first();
        if (! $assign_class) {
            return $this->responseError([], _lang('Class Assign not found.'), 404);
        }

        return $this->responseSuccess($assign_class, 'Class Assign have been fetched successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        $teacher_id = (int) $request->teacher_id;
        $class_id = (int) $request->class_id;

        $assign_class = ClassAssign::where('id', $id)->first();
        if (! $assign_class) {
            return $this->responseError([], _lang('Class Assign not found.'), 404);
        }

        DB::beginTransaction();
        try {
            $assign_class->teacher_id = $teacher_id;
            $assign_class->class_id = $class_id;
            $assign_class->update();

            DB::commit();

            return $this->responseSuccess($assign_class, 'Class Assign Updated successfully.');
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError([], 'Failed to insert records unsuccessfully');
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $assign_class = ClassAssign::where('id', $id)->first();
        if (! empty($assign_class)) {
            $assign_class->delete();

            return $this->responseSuccess($assign_class, 'Class Assign Remove successfully.');
        } else {
            return $this->responseError([], 'Something went wrong');
        }
    }
}

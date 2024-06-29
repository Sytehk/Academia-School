<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Section;

class APISectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $classId = (int) $request->class_id;

        $query = Section::select('sections.id AS id', 'sections.section_name', 'classes.class_name', 'student_groups.group_name', 'teachers.name as teacher_name')
            ->leftJoin('teachers', 'teachers.id', '=', 'sections.class_teacher_id')
            ->leftJoin('classes', 'classes.id', '=', 'sections.class_id')
            ->leftJoin('student_groups', 'sections.student_group_id', '=', 'student_groups.id');

        // Apply filter if class_id is provided
        if ($classId) {
            $query->where('sections.class_id', $classId);
        }

        $sections = $query->orderBy('sections.id', 'ASC')->paginate($perPage);

        return $this->responseSuccess($sections, 'Sections have been fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'section_name' => 'required|string|max:191',
            'class_id' => 'required|exists:classes,id',
            'student_group_id' => 'nullable|exists:student_groups,id',
            'class_teacher_id' => 'nullable|string|max:191',
            'room_no' => 'nullable|string|max:191',
            'rank' => 'nullable|string|max:191',
            'capacity' => 'nullable|integer|min:1',
            'attendance_time_config_id' => 'nullable|exists:attendance_time_configs,id',
        ]);

        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        // Check for duplicate section in same institute/branch/class/student_group
        $exists = Section::where('section_name', $request->section_name)
            ->where('class_id', $request->class_id)
            ->where('student_group_id', $request->student_group_id)
            ->where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->first();

        if ($exists) {
            return $this->responseError([], _lang('This section already exists in this class/group.'));
        }

        $section = new Section;
        $section->institute_id = $instituteId;
        $section->branch_id = $branchId;
        $section->section_name = $request->section_name;
        $section->class_id = (int) $request->class_id;
        $section->student_group_id = (int) $request->student_group_id;
        $section->class_teacher_id = $request->class_teacher_id ? (int) $request->class_teacher_id : null;
        $section->room_no = $request->room_no;
        $section->rank = $request->rank;
        $section->capacity = $request->capacity;
        $section->attendance_time_config_id = $request->attendance_time_config_id;
        $section->save();

        return $this->responseSuccess([], _lang('Section has been added successfully.'));
    }

    public function show(int $id): JsonResponse
    {
        $section = Section::select('*', 'sections.id AS id')
            ->join('classes', 'classes.id', '=', 'sections.class_id')
            ->where('sections.id', $id)
            ->first();
        if (! $section) {
            return $this->responseError([], _lang('Something went wrong. Section can not be found.'), 404);
        }

        return $this->responseSuccess($section, 'Sections has been fetch.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'section_name' => 'required|string|max:191',
            'class_id' => 'required|exists:classes,id',
            'student_group_id' => 'nullable|exists:student_groups,id',
            'class_teacher_id' => 'nullable|string|max:191',
            'room_no' => 'nullable|string|max:191',
            'rank' => 'nullable|string|max:191',
            'capacity' => 'nullable|integer|min:1',
            'attendance_time_config_id' => 'nullable|exists:attendance_time_configs,id',
        ]);

        $section = Section::find($id);
        if (! $section) {
            return $this->responseError([], _lang('Something went wrong. Section cannot be found.'), 404);
        }

        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        // Check for duplicate section excluding current
        $exists = Section::where('section_name', $request->section_name)
            ->where('class_id', $request->class_id)
            ->where('student_group_id', $request->student_group_id)
            ->where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return $this->responseError([], _lang('Another section with this name already exists in this class/group.'));
        }

        $section->section_name = $request->section_name;
        $section->class_id = (int) $request->class_id;
        $section->student_group_id = (int) $request->student_group_id;
        $section->class_teacher_id = $request->class_teacher_id ? (int) $request->class_teacher_id : null;
        $section->room_no = $request->room_no;
        $section->rank = $request->rank;
        $section->capacity = $request->capacity;
        $section->attendance_time_config_id = $request->attendance_time_config_id;
        $section->save();

        return $this->responseSuccess([], _lang('Section has been updated successfully.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $section = Section::find($id);
        if (! $section) {
            return $this->responseError([], _lang('Something went wrong. Section can not be found.'), 404);
        }
        if ($section->students) {
            return $this->responseError([], _lang('Section cannot be deleted because it has assigned students.'));
        }
        $section->delete();

        return $this->responseSuccess([], 'Sections has been deleted.');
    }
}

<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Academic\Models\StudentGroup;

class APIStudentGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $studentGroups = StudentGroup::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);

        return $this->responseSuccess($studentGroups, 'Student Groups have been fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'group_name' => [
                'required',
                'max:100',
                Rule::unique('student_groups')
                    ->where('institute_id', get_institute_id())
                    ->where('branch_id', get_branch_id()),
            ],
        ]);

        $studentGroup = new StudentGroup;
        $studentGroup->institute_id = get_institute_id();
        $studentGroup->branch_id = get_branch_id();
        $studentGroup->group_name = $request->group_name;
        $studentGroup->save();

        return $this->responseSuccess($studentGroup, _lang('Student Group has been added.'));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $studentGroup = StudentGroup::find($id);

        return $this->responseSuccess($studentGroup, 'Student Groups has been fetch.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $studentGroup = StudentGroup::find($id);
        if (! $studentGroup) {
            return $this->responseError([], _lang('Student Group not found.'), 404);
        }

        $request->validate([
            'group_name' => [
                'required',
                'max:100',
                Rule::unique('student_groups')
                    ->where('institute_id', get_institute_id())
                    ->where('branch_id', get_branch_id())
                    ->ignore($id),
            ],
        ]);

        $studentGroup->update([
            'group_name' => $request->group_name,
        ]);

        return $this->responseSuccess($studentGroup, _lang('Student Group has been updated.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $studentGroup = StudentGroup::find($id);
        if (! $studentGroup) {
            return $this->responseError([], _lang('Something went wrong. Group can not be found.'), 404);
        }
        if ($studentGroup->sections) {
            return $this->responseError([], _lang('Group cannot be deleted because it has assigned sections.'));
        }
        $studentGroup->delete();

        return $this->responseSuccess([], 'Student Groups has been deleted.');
    }
}

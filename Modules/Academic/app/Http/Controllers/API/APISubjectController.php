<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Subject;

class APISubjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $classId = (int) $request->class_id;

        $query = Subject::select('*', 'subjects.id AS id')
            ->join('classes', 'classes.id', '=', 'subjects.class_id');

        // Apply filter if class_id is provided
        if ($classId) {
            $query->where('subjects.class_id', $classId);
        }

        $subjects = $query->orderBy('subjects.id', 'ASC')->paginate($perPage);

        return $this->responseSuccess($subjects, 'Subjects have been fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'required|exists:student_groups,id',
            'subject_name' => 'required|string|max:191',
            'subject_code' => 'required|string|max:191',
            'subject_short_form' => 'nullable|string|max:191',
            'subject_type' => 'nullable',
            'subject_type_form' => 'nullable',
        ]);

        $exists = Subject::where('class_id', $request->class_id)
            ->where('group_id', $request->group_id)
            ->where(function ($query) use ($request) {
                $query->where('subject_code', $request->subject_code)
                    ->orWhere('subject_name', $request->subject_name);
            })
            ->exists();

        if ($exists) {
            return $this->responseError([], _lang('Subject Name or Code Already Exists For This Class & Group'));
        }

        $subject = new Subject;
        $subject->institute_id = get_institute_id();
        $subject->branch_id = get_branch_id();
        $subject->class_id = $request->class_id;
        $subject->group_id = $request->group_id;
        $subject->subject_name = $request->subject_name;
        $subject->subject_code = $request->subject_code;
        $subject->subject_short_form = $request->subject_short_form;
        $subject->subject_type = $request->subject_type;
        $subject->subject_type_form = $request->subject_type_form;
        $subject->sl_no = $request->sl_no;
        $subject->objective = $request->objective;
        $subject->written = $request->written;
        $subject->practical = $request->practical;
        $subject->full_mark = $request->full_mark;
        $subject->pass_mark = $request->pass_mark;
        $subject->save();

        return $this->responseSuccess([], 'Subject has been added.');
    }

    public function show(int $id): JsonResponse
    {
        $subject = Subject::find($id);
        if (! $subject) {
            return $this->responseError([], _lang('Something went wrong. subject can not be found.'), 404);
        }

        return $this->responseSuccess($subject, 'Subject has been fetch.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'required|exists:student_groups,id',
            'subject_name' => 'required|string|max:191',
            'subject_code' => 'required|string|max:191',
            'subject_short_form' => 'nullable|string|max:191',
            'subject_type' => 'nullable',
            'subject_type_form' => 'nullable',
        ]);

        $subject = Subject::find($id);
        if (! $subject) {
            return $this->responseError([], _lang('Something went wrong. subject can not be found.'), 404);
        }
        $subject->class_id = $request->class_id;
        $subject->group_id = $request->group_id;
        $subject->subject_name = $request->subject_name;
        $subject->subject_code = $request->subject_code;
        $subject->subject_short_form = $request->subject_short_form;
        $subject->subject_type = $request->subject_type;
        $subject->subject_type_form = $request->subject_type_form;
        $subject->sl_no = $request->sl_no;
        $subject->objective = $request->objective;
        $subject->written = $request->written;
        $subject->practical = $request->practical;
        $subject->full_mark = $request->full_mark;
        $subject->pass_mark = $request->pass_mark;
        $subject->save();

        return $this->responseSuccess([], 'Subject has been updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $subject = Subject::find($id);
        if (! $subject) {
            return $this->responseError([], _lang('Something went wrong. subject can not be found.'), 404);
        }
        $subject->delete();

        return $this->responseSuccess([], 'Subject has been deleted.');
    }
}

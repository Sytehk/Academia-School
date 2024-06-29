<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Syllabus;

class APISyllabusController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $syllabus = Syllabus::select('*', 'syllabus.id AS id')
            ->join('classes', 'classes.id', '=', 'syllabus.class_id')
            ->where('syllabus.session_id', get_option('academic_year'))
            ->where('syllabus.institute_id', get_institute_id())
            ->where('syllabus.branch_id', get_branch_id())
            ->orderBy('syllabus.id', 'DESC')
            ->paginate($perPage);

        return $this->responseSuccess($syllabus, 'Syllabus has been fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'file' => 'required|mimes:doc,pdf,docx,zip',
        ]);

        $syllabus = new Syllabus;
        $syllabus->institute_id = get_institute_id();
        $syllabus->branch_id = get_branch_id();
        $syllabus->session_id = get_option('academic_year');
        $syllabus->title = $request->title;
        $syllabus->description = $request->description;
        $syllabus->class_id = $request->class_id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file_name = time().'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/syllabus/'), $file_name);
            $syllabus->file = $file_name;
        }

        $syllabus->save();

        return $this->responseSuccess([], 'Syllabus has been added.');
    }

    public function show(int $id): JsonResponse
    {
        $syllabus = Syllabus::select('*', 'syllabus.id AS id')
            ->join('classes', 'classes.id', '=', 'syllabus.class_id')
            ->where('syllabus.id', $id)
            ->where('syllabus.session_id', get_option('academic_year'))
            ->where('syllabus.institute_id', get_institute_id())
            ->where('syllabus.branch_id', get_branch_id())
            ->first();

        if (! $syllabus) {
            return $this->responseError([], _lang('Something went wrong. syllabus can not be found.'), 404);
        }

        return $this->responseSuccess($syllabus, 'Syllabus has been fetch.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'file' => 'nullable|mimes:doc,pdf,docx,zip',
        ]);

        $syllabus = Syllabus::find($id);
        if (! $syllabus) {
            return $this->responseError([], _lang('Something went wrong. syllabus can not be found.'), 404);
        }

        $syllabus->session_id = get_option('academic_year');
        $syllabus->title = $request->title;
        $syllabus->description = $request->description;
        $syllabus->class_id = $request->class_id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file_name = time().'.'.$file->getClientOriginalExtension();
            $file->move(base_path('public/uploads/files/syllabus/'), $file_name);
            $syllabus->file = $file_name;
        }

        $syllabus->save();

        return $this->responseSuccess([], 'Syllabus has been updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $syllabus = Syllabus::find($id);
        if (! $syllabus) {
            return $this->responseError([], _lang('Something went wrong. syllabus can not be found.'), 404);
        }

        $syllabus->delete();

        return $this->responseSuccess([], 'Syllabus has been deleted.');
    }
}

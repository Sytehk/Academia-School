<?php

namespace Modules\Teacher\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Teacher\DataTables\ClassLesson\ClassLessonDataTable;
use Modules\Teacher\Http\Requests\ClassLesson\ClassLessonStoreRequest;
use Modules\Teacher\Http\Requests\ClassLesson\ClassLessonUpdateRequest;
use Modules\Teacher\Models\ClassLesson;
use Modules\Teacher\Services\ClassLesson\ClassLessonService;
use RealRashid\SweetAlert\Facades\Alert;

class ClassLessonController extends Controller
{
    public function __construct(
        private ClassLessonService $service
    ) {}

    public function index(ClassLessonDataTable $dataTable): mixed
    {
        return $dataTable->render('teacher::classlesson.index');
    }

    public function create(): Factory|View
    {
        return view('teacher::classlesson.create');
    }

    public function store(ClassLessonStoreRequest $request): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $classLesson = new ClassLesson;
            $classLesson->institute_id = get_institute_id();
            $classLesson->branch_id = get_branch_id();
            $classLesson->session_id = get_option('academic_year');
            $classLesson->title = $request->title;
            $classLesson->note = $request->note;
            $classLesson->date = $request->date;
            $classLesson->class_id = $request->class_id;
            $classLesson->section_id = $request->section_id;
            $classLesson->subject_id = $request->subject_id;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $file_name = secure_public_move($file, 'uploads/files/class_lessons/');
                $classLesson->file = $file_name;
            }
            if ($request->hasFile('file_2')) {
                $file = $request->file('file_2');
                $file_name = secure_public_move($file, 'uploads/files/class_lessons/');
                $classLesson->file_2 = $file_name;
            }
            if ($request->hasFile('file_3')) {
                $file = $request->file('file_3');
                $file_name = secure_public_move($file, 'uploads/files/class_lessons/');
                $classLesson->file_3 = $file_name;
            }
            if ($request->hasFile('file_4')) {
                $file = $request->file('file_4');
                $file_name = secure_public_move($file, 'uploads/files/class_lessons/');
                $classLesson->file_4 = $file_name;
            }

            $classLesson->save();

            DB::commit();

            Alert::toast('ClassLesson has been create successfully.', 'success');

            return redirect('classlessons');
        } catch (Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('classlessons');
        }
    }

    public function edit(int $id): Factory|View
    {
        $classlesson = $this->service->getById($id);

        return view('teacher::classlesson.edit', compact('classlesson'));
    }

    public function update(ClassLessonUpdateRequest $request, int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $classLesson = ClassLesson::find($id);
            if (! $classLesson) {
                return back()->with('error', _lang('No Data Found!'));
            }
            $classLesson->institute_id = get_institute_id();
            $classLesson->branch_id = get_branch_id();
            $classLesson->session_id = get_option('academic_year');
            $classLesson->title = $request->title;
            $classLesson->note = $request->note;
            $classLesson->date = $request->date;
            $classLesson->class_id = $request->class_id;
            $classLesson->section_id = $request->section_id;
            $classLesson->subject_id = $request->subject_id;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $file_name = secure_public_move($file, 'uploads/files/class_lessons/');
                $classLesson->file = $file_name;
            }
            if ($request->hasFile('file_2')) {
                $file = $request->file('file_2');
                $file_name = secure_public_move($file, 'uploads/files/class_lessons/');
                $classLesson->file_2 = $file_name;
            }
            if ($request->hasFile('file_3')) {
                $file = $request->file('file_3');
                $file_name = secure_public_move($file, 'uploads/files/class_lessons/');
                $classLesson->file_3 = $file_name;
            }
            if ($request->hasFile('file_4')) {
                $file = $request->file('file_4');
                $file_name = secure_public_move($file, 'uploads/files/class_lessons/');
                $classLesson->file_4 = $file_name;
            }

            $classLesson->save();

            DB::commit();
            Alert::toast('ClassLesson has been update successfully.', 'success');

            return redirect('classlessons');
        } catch (Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('classlessons');
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $this->service->delete($id);

            DB::commit();
            Alert::toast('ClassLesson has been delete successfully.', 'success');

            return redirect('classlessons');
        } catch (Exception $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());

            return redirect('classlessons');
        }
    }
}

<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Exam;
use RealRashid\SweetAlert\Facades\Alert;

class ExamController extends Controller
{
    public function index(): Factory|View
    {
        $exams = Exam::orderBy('id', 'desc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();

        return view('academic::exam.index', compact('exams'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'name' => 'required|max:255',
            'exam_code' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $exam = new Exam;
            $exam->institute_id = get_institute_id();
            $exam->branch_id = get_branch_id();
            $exam->name = $request->name;
            $exam->exam_code = $request->exam_code;
            $exam->save();
            DB::commit();

            Alert::toast(_lang('Exam has been create successfully'), 'success');

            return redirect('exams');
        } catch (\Exception $e) {
            DB::rollback();

            Alert::error('Error', $e->getMessage());

            return redirect('exams');
        }
    }

    public function edit(int $id): Factory|View
    {
        $exam = Exam::where('id', $id)->first();

        return view('academic::exam.edit', compact('exam'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'name' => 'required|max:255',
            'exam_code' => 'nullable|numeric',
        ]);

        $exam = Exam::where('id', $id)->first();

        DB::beginTransaction();
        try {
            $exam->name = $request->name;
            $exam->exam_code = $request->exam_code;
            $exam->update();
            DB::commit();

            Alert::toast(_lang('Exam has been update successfully'), 'success');

            return redirect('exams');
        } catch (\Exception $e) {
            DB::rollback();

            Alert::error('Error', $e->getMessage());

            return redirect('exams');
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $exam = Exam::where('id', $id)->first();
        if (! empty($exam)) {
            $exam->delete();

            Alert::toast(_lang('Exam has been delete successfully'), 'success');

            return redirect('exams');
        } else {
            Alert::error('Error', _lang('Something went wrong. Exam can not be delete.'));

            return redirect('exams');
        }
    }
}

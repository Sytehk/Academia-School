<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\ClassAssign;
use RealRashid\SweetAlert\Facades\Alert;

class ClassAssignController extends Controller
{
    public function index(): Factory|View
    {
        $class_assigns = DB::table('class_assigns')
            ->join('teachers', 'class_assigns.teacher_id', '=', 'teachers.id')
            ->join('classes', 'class_assigns.class_id', '=', 'classes.id')
            ->select('teachers.name as teacher_name', 'classes.class_name as class_name', 'class_assigns.id as id', 'class_assigns.teacher_id', 'class_assigns.class_id')
            ->get();

        return view('academic::class_assign.index', compact('class_assigns'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'teacher_id' => 'required',
            'class_id' => 'required',
        ]);

        $teacher_id = $request->teacher_id;
        $class_id = $request->class_id;

        DB::beginTransaction();
        try {
            $exists = ClassAssign::where('teacher_id', $teacher_id)
                ->where('class_id', $class_id)
                ->exists();
            if ($exists) {
                return back()->with('error', _lang('Class Already Added to this teacher'));
            } else {
                $assign_class = new ClassAssign;
                $assign_class->institute_id = get_institute_id();
                $assign_class->branch_id = get_branch_id();
                $assign_class->teacher_id = $teacher_id;
                $assign_class->class_id = $class_id;
                $assign_class->save();
                DB::commit();

                Alert::toast(_lang('Class Assign has been create successfully'), 'success');

                return redirect('assign-class');
            }
        } catch (\Exception $e) {
            DB::rollback();

            Alert::error('Error', $e->getMessage());

            return redirect('assign-class');
        }
    }

    public function edit(int $id): Factory|View
    {
        $assign_class = ClassAssign::where('id', $id)->first();

        return view('academic::class_assign.edit', compact('assign_class'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'teacher_id' => 'required',
            'class_id' => 'required',
        ]);

        $teacher_id = $request->teacher_id;
        $class_id = $request->class_id;

        $assign_class = ClassAssign::where('id', $id)->first();

        DB::beginTransaction();
        try {
            $assign_class->teacher_id = $teacher_id;
            $assign_class->class_id = $class_id;
            $assign_class->update();

            DB::commit();

            Alert::toast(_lang('Class Assign has been updated successfully'), 'success');

            return redirect('assign-class');
        } catch (\Exception $e) {
            DB::rollback();

            Alert::error('Error', $e->getMessage());

            return redirect('assign-class');
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $assign_class = ClassAssign::where('id', $id)->first();
        if (! empty($assign_class)) {
            $assign_class->delete();

            Alert::toast(_lang('Class Assign has been delete successfully'), 'success');

            return redirect('assign-class');
        } else {
            Alert::error('Error', _lang('Something went wrong'));

            return redirect('assign-class');
        }
    }
}

<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\AssignShift;
use Modules\Academic\Models\Shift;
use Modules\Academic\Models\Teacher;
use RealRashid\SweetAlert\Facades\Alert;

class AssignShiftController extends Controller
{
    public function index(): Factory|View
    {
        $teachers = Teacher::all();
        $shifts = Shift::all();
        $assign_shifts = DB::table('assign_shifts')
            ->join('teachers', 'assign_shifts.teacher_id', '=', 'teachers.id')
            ->join('shifts', 'assign_shifts.shift_id', '=', 'shifts.id')
            ->select('teachers.name as teacher_name', 'shifts.name as shift_name', 'assign_shifts.id as id', 'assign_shifts.teacher_id', 'assign_shifts.shift_id')
            ->get();

        return view('academic::shift_assign.index', compact('teachers', 'shifts', 'assign_shifts'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'teacher_id' => 'required',
            'shift_id' => 'required',
        ]);

        $teacher_id = $request->teacher_id;
        $shift_id = $request->shift_id;

        DB::beginTransaction();
        try {
            $exists = AssignShift::where('teacher_id', $teacher_id)
                ->where('shift_id', $shift_id)
                ->exists();
            if ($exists) {
                return back()->with('error', _lang('Shift Already Added to this teacher'));
            } else {
                $assign_shift = new AssignShift;
                $assign_shift->institute_id = get_institute_id();
                $assign_shift->branch_id = get_branch_id();
                $assign_shift->teacher_id = $teacher_id;
                $assign_shift->shift_id = $shift_id;
                $assign_shift->save();
                DB::commit();

                Alert::toast('Shift Assign create successfully.', 'success');

                return redirect()->route('assign-shifts.index');
            }
        } catch (\Exception $e) {
            DB::rollback();

            Alert::error('Error', $e->getMessage());

            return redirect()->route('assign-shifts.index');
        }
    }

    public function edit(int $id): Factory|View
    {
        $assign_shift = AssignShift::where('id', $id)->first();
        $teachers = Teacher::all();
        $shifts = Shift::all();

        return view('academic::shift_assign.edit', compact('teachers', 'shifts', 'assign_shift'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'teacher_id' => 'required',
            'shift_id' => 'required',
        ]);

        $teacher_id = $request->teacher_id;
        $shift_id = $request->shift_id;

        $assign_shift = AssignShift::where('id', $id)->first();

        DB::beginTransaction();
        try {
            $assign_shift->teacher_id = $teacher_id;
            $assign_shift->shift_id = $shift_id;
            $assign_shift->update();

            DB::commit();

            Alert::toast('Shift Assign updated successfully.', 'success');

            return redirect()->route('assign-shifts.index');
        } catch (\Exception $e) {
            DB::rollback();

            Alert::error('Error', $e->getMessage());

            return redirect()->route('assign-shifts.index');
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $assign_shift = AssignShift::where('id', $id)->first();
        if (! empty($assign_shift)) {
            $assign_shift->delete();

            Alert::toast('Shift Assign delete successfully.', 'success');

            return redirect()->route('assign-shifts.index');
        } else {
            Alert::error('Error', _lang('Failed to delete records unsuccessfully'));

            return redirect()->route('assign-shifts.index');
        }
    }
}

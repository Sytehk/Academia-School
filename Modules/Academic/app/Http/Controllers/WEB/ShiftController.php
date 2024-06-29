<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Shift;
use RealRashid\SweetAlert\Facades\Alert;

class ShiftController extends Controller
{
    public function index(): Factory|View
    {
        $shifts = Shift::orderBy('id', 'desc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();

        return view('academic::shift.index', compact('shifts'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        DB::beginTransaction();
        try {
            $shift = new Shift;
            $shift->institute_id = get_institute_id();
            $shift->branch_id = get_branch_id();
            $shift->name = $request->name;
            $shift->save();
            DB::commit();

            Alert::toast(_lang('Shift has been create successfully'), 'success');

            return redirect('shift');
        } catch (\Exception $e) {
            DB::rollback();

            Alert::error('Error', $e->getMessage());

            return redirect('shift');
        }
    }

    public function edit(int $id): Factory|View
    {
        $shift = Shift::where('id', $id)->first();

        return view('academic::shift.edit', compact('shift'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);

        $shift = Shift::where('id', $id)->first();

        DB::beginTransaction();
        try {
            $shift->name = $request->name;
            $shift->update();
            DB::commit();

            Alert::toast(_lang('Shift has been update successfully'), 'success');

            return redirect('shift');
        } catch (\Exception $e) {
            DB::rollback();

            Alert::error('Error', $e->getMessage());

            return redirect('shift');
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $shift = Shift::where('id', $id)->first();
        if (! empty($shift)) {
            $shift->delete();

            Alert::toast(_lang('Shift has been delete successfully'), 'success');

            return redirect('shift');
        } else {
            Alert::error('Error', _lang('Something went wrong. Shift can not be delete.'));

            return redirect('shift');
        }
    }
}

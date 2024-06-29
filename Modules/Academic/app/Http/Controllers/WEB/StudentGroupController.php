<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Models\StudentGroup;
use RealRashid\SweetAlert\Facades\Alert;

class StudentGroupController extends Controller
{
    public function index(): Factory|View
    {
        $studentgroups = StudentGroup::all()->sortByDesc('id');

        return view('academic::student_group.list', compact('studentgroups'));
    }

    public function create(Request $request): Factory|View
    {
        return view('academic::student_group.create');
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'group_name' => 'required|max:100',
        ]);

        $studentgroup = new StudentGroup;
        $studentgroup->institute_id = get_institute_id();
        $studentgroup->branch_id = get_branch_id();
        $studentgroup->group_name = $request->input('group_name');

        $studentgroup->save();

        Alert::toast('Student Group has been create successfully.', 'success');

        return redirect('student-groups');
    }

    public function show(Request $request, int $id): Factory|View
    {
        $studentgroup = StudentGroup::find($id);

        return view('academic::student_group.view', compact('studentgroup', 'id'));
    }

    public function edit(Request $request, int $id): Factory|View
    {
        $studentgroup = StudentGroup::find($id);

        return view('academic::student_group.edit', compact('studentgroup', 'id'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'group_name' => 'required|max:100',
        ]);

        $studentgroup = StudentGroup::find($id);
        $studentgroup->group_name = $request->input('group_name');

        $studentgroup->save();

        Alert::toast('Student Group has been update successfully.', 'success');

        return redirect('student-groups');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $studentgroup = StudentGroup::find($id);
        $studentgroup->delete();

        Alert::toast('Student Group has been delete successfully.', 'success');

        return redirect('student-groups');
    }
}

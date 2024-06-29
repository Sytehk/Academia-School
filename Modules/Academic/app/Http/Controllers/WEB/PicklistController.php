<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Models\Picklist;
use RealRashid\SweetAlert\Facades\Alert;

class PicklistController extends Controller
{
    public function index(): Factory|View
    {
        $type = '';
        $picklists = Picklist::all()->sortByDesc('id');

        return view('academic::picklist.list', compact('picklists', 'type'));
    }

    public function type($type = ''): Factory|View
    {
        $picklists = Picklist::where('type', $type)->orderBy('id', 'desc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();

        return view('academic::picklist.list', compact('picklists', 'type'));
    }

    public function create(Request $request): Factory|View
    {
        return view('academic::picklist.create');
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'type' => 'required',
            'value' => 'required',
        ]);
        $picklist = new Picklist;
        $picklist->institute_id = get_institute_id();
        $picklist->branch_id = get_branch_id();
        $picklist->type = $request->input('type');
        $picklist->value = $request->input('value');

        $picklist->save();

        Alert::toast('Picklist has been create successfully.', 'success');

        return redirect('picklists');
    }

    public function show(Request $request, int $id): Factory|View
    {
        $picklist = Picklist::find($id);

        return view('academic::picklist.view', compact('picklist', 'id'));
    }

    public function edit(Request $request, int $id): Factory|View
    {
        $picklist = Picklist::find($id);

        return view('academic::picklist.edit', compact('picklist', 'id'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'type' => 'required',
            'value' => 'required',
        ]);

        $picklist = Picklist::find($id);
        $picklist->type = $request->input('type');
        $picklist->value = $request->input('value');

        $picklist->save();

        Alert::toast('Picklist has been update successfully.', 'success');

        return redirect('picklists');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $picklist = Picklist::find($id);
        $picklist->delete();

        Alert::toast('Picklist has been delete successfully.', 'success');

        return redirect('picklists');
    }
}

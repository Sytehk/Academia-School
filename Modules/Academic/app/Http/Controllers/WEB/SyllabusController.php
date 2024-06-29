<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Modules\Academic\Models\Syllabus;
use RealRashid\SweetAlert\Facades\Alert;

class SyllabusController extends Controller
{
    public function index(): Factory|View
    {
        $syllabus = Syllabus::select('*', 'syllabus.id AS id')
            ->join('classes', 'classes.id', '=', 'syllabus.class_id')
            ->where('syllabus.session_id', get_option('academic_year'))
            ->where('syllabus.institute_id', get_institute_id())
            ->where('syllabus.branch_id', get_branch_id())
            ->orderBy('syllabus.id', 'DESC')
            ->get();

        return view('academic::syllabus.syllabus-list', compact('syllabus'));
    }

    public function create(): Factory|View
    {
        return view('academic::syllabus.syllabus-add');
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'class_id' => 'required',
            'file' => 'required|mimes:doc,pdf,docx,zip',
        ]);

        $syllabus = new Syllabus;
        $syllabus->institute_id = get_institute_id();
        $syllabus->branch_id = get_branch_id();
        $syllabus->session_id = get_option('academic_year');
        $syllabus->title = $request->title;
        $syllabus->description = sanitize_rich_text($request->description);
        $syllabus->class_id = $request->class_id;

        if ($request->hasFile('file')) {
            try {
                $syllabus->file = safeDocumentUploader(
                    base_path('public/uploads/files/syllabus/'),
                    $request->file('file'),
                    ['doc', 'docx', 'pdf', 'zip']
                );
            } catch (\Exception $e) {
                Log::error('Syllabus file upload rejected: '.$e->getMessage());
                Alert::error('Error', _lang('The uploaded file could not be verified as a valid document.'));

                return back()->withInput();
            }
        }

        $syllabus->save();

        Alert::toast('Information has been added!', 'success');

        return redirect('syllabus');
    }

    public function show(int $id): Factory|View
    {
        $syllabus = Syllabus::select('*', 'syllabus.id AS id')
            ->join('classes', 'classes.id', '=', 'syllabus.class_id')
            ->where('syllabus.id', $id)
            ->where('syllabus.session_id', get_option('academic_year'))
            ->where('syllabus.institute_id', get_institute_id())
            ->where('syllabus.branch_id', get_branch_id())
            ->first();

        return view('academic::syllabus.syllabus-view', compact('syllabus'));
    }

    public function edit(int $id): Factory|View
    {
        $syllabus = Syllabus::where('id', $id)
            ->where('session_id', get_option('academic_year'))
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->first();

        return view('academic::syllabus.syllabus-edit', compact('syllabus'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'class_id' => 'required',
            'file' => 'nullable|mimes:doc,pdf,docx,zip',
        ]);

        $syllabus = Syllabus::where('id', $id)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->first();

        if (! $syllabus) {
            Alert::error('Error', _lang('Syllabus not found.'));

            return redirect('syllabus');
        }

        $syllabus->session_id = get_option('academic_year');
        $syllabus->title = $request->title;
        $syllabus->description = sanitize_rich_text($request->description);
        $syllabus->class_id = $request->class_id;

        if ($request->hasFile('file')) {
            try {
                $syllabus->file = safeDocumentUploader(
                    base_path('public/uploads/files/syllabus/'),
                    $request->file('file'),
                    ['doc', 'docx', 'pdf', 'zip'],
                    $syllabus->file
                );
            } catch (\Exception $e) {
                Log::error('Syllabus file upload rejected: '.$e->getMessage());
                Alert::error('Error', _lang('The uploaded file could not be verified as a valid document.'));

                return back()->withInput();
            }
        }

        $syllabus->save();

        Alert::toast('Information has been updated!', 'success');

        return redirect('syllabus');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $syllabus = Syllabus::where('id', $id)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->first();

        if (! $syllabus) {
            Alert::error('Error', _lang('Syllabus not found.'));

            return redirect('syllabus');
        }

        $syllabus->delete();

        Alert::toast('Information has been deleted!', 'success');

        return redirect('syllabus');
    }
}

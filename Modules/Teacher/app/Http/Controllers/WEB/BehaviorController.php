<?php

namespace Modules\Teacher\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\StudentSession;
use Modules\Teacher\DataTables\Behavior\BehaviorDataTable;
use Modules\Teacher\Http\Requests\Behavior\BehaviorStoreRequest;
use Modules\Teacher\Http\Requests\Behavior\BehaviorUpdateRequest;
use Modules\Teacher\Models\Behavior;
use Modules\Teacher\Services\Behavior\BehaviorService;
use RealRashid\SweetAlert\Facades\Alert;

class BehaviorController extends Controller
{
    public function __construct(
        private BehaviorService $service
    ) {}

    public function index(BehaviorDataTable $dataTable): mixed
    {
        return $dataTable->render('teacher::behavior.index');
    }

    public function create(): Factory|View
    {
        return view('teacher::behavior.create');
    }

    public function store(BehaviorStoreRequest $request)
    {
        DB::beginTransaction();

        try {

            $behavior = new Behavior;

            $behavior->institute_id = get_institute_id();
            $behavior->branch_id = get_branch_id();

            $behavior->session_id = get_option('academic_year');

            $behavior->title = $request->title;
            $behavior->date = $request->date;
            $behavior->student_id = $request->student_id;
            $behavior->class_id = $request->class_id;
            $behavior->section_id = $request->section_id;
            $behavior->note = $request->note;

            if ($request->hasFile('file')) {

                $file = $request->file('file');

                $fileName = time().'_'.$file->getClientOriginalName();

                $file->move(
                    public_path('uploads/files/behaviors'),
                    $fileName
                );

                $behavior->file = $fileName;
            }

            $behavior->save();

            DB::commit();

            Alert::success('Success', 'Behavior created successfully.');

            return redirect()->route('behaviors.index');
        } catch (Exception $e) {

            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return back();
        }
    }

    public function edit(int $id): Factory|View
    {
        $behavior = $this->service->getById($id);

        return view('teacher::behavior.edit', compact('behavior'));
    }

    public function update(
        BehaviorUpdateRequest $request,
        int $id
    ) {

        DB::beginTransaction();

        try {

            $behavior = Behavior::findOrFail($id);

            $behavior->title = $request->title;
            $behavior->date = $request->date;
            $behavior->student_id = $request->student_id;
            $behavior->class_id = $request->class_id;
            $behavior->section_id = $request->section_id;
            $behavior->note = $request->note;

            if ($request->hasFile('file')) {

                if (
                    $behavior->file &&
                    file_exists(public_path('uploads/files/behaviors/'.$behavior->file))
                ) {
                    unlink(public_path('uploads/files/behaviors/'.$behavior->file));
                }

                $file = $request->file('file');

                $fileName = time().'_'.$file->getClientOriginalName();

                $file->move(
                    public_path('uploads/files/behaviors'),
                    $fileName
                );

                $behavior->file = $fileName;
            }

            $behavior->save();

            DB::commit();

            Alert::success('Success', 'Behavior updated successfully.');

            return redirect()->route('behaviors.index');
        } catch (Exception $e) {

            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return back();
        }
    }

    public function destroy(int $id)
    {
        DB::beginTransaction();

        try {

            $behavior = Behavior::findOrFail($id);

            if (
                $behavior->file &&
                file_exists(public_path('uploads/files/behaviors/'.$behavior->file))
            ) {
                unlink(public_path('uploads/files/behaviors/'.$behavior->file));
            }

            $behavior->delete();

            DB::commit();

            Alert::success('Success', 'Behavior deleted successfully.');

            return redirect()->route('behaviors.index');
        } catch (Exception $e) {

            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return back();
        }
    }

    public function getStudents()
    {
        $students = StudentSession::with('student')
            ->where('session_id', get_option('academic_year'))
            ->where('class_id', request('class_id'))
            ->where('section_id', request('section_id'))
            ->orderBy('roll')
            ->get();

        $output = '<option value="">'._lang('Select Student').'</option>';

        foreach ($students as $row) {

            $name = $row->student?->first_name.' '.$row->student?->last_name;

            $output .= '
            <option value="'.$row->student_id.'">
                '.$name.' (Roll-'.$row->roll.')
            </option>
        ';
        }

        return $output;
    }
}

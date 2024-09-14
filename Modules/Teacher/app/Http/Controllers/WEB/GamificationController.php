<?php

namespace Modules\Teacher\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Teacher\DataTables\Gamification\GamificationDataTable;
use Modules\Teacher\Http\Requests\Gamification\GamificationStoreRequest;
use Modules\Teacher\Http\Requests\Gamification\GamificationUpdateRequest;
use Modules\Teacher\Models\Gamification;
use Modules\Teacher\Services\Gamification\GamificationService;
use RealRashid\SweetAlert\Facades\Alert;

class GamificationController extends Controller
{
    public function __construct(
        private GamificationService $service
    ) {}

    public function index(GamificationDataTable $dataTable): mixed
    {
        return $dataTable->render('teacher::gamification.index');
    }

    public function create(): Factory|View
    {
        return view('teacher::gamification.create');
    }

    public function store(GamificationStoreRequest $request)
    {
        DB::beginTransaction();

        try {

            $gamification = new Gamification;

            $gamification->institute_id = get_institute_id();
            $gamification->branch_id = get_branch_id();
            $gamification->session_id = get_option('academic_year');

            $gamification->title = $request->title;
            $gamification->date = $request->date;
            $gamification->student_id = $request->student_id;
            $gamification->class_id = $request->class_id;
            $gamification->section_id = $request->section_id;
            $gamification->note = $request->note;

            if ($request->hasFile('file')) {

                $file = $request->file('file');

                $filename = time().'_'.$file->getClientOriginalName();

                $file->move(
                    public_path('uploads/files/gamification'),
                    $filename
                );

                $gamification->file = $filename;
            }

            $gamification->save();

            DB::commit();

            Alert::success('Success', 'Gamification created successfully.');

            return redirect()->route('gamifications.index');
        } catch (Exception $e) {

            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return back();
        }
    }

    public function edit(int $id): Factory|View
    {
        $gamification = $this->service->getById($id);

        return view('teacher::gamification.edit', compact('gamification'));
    }

    public function update(
        GamificationUpdateRequest $request,
        int $id
    ) {
        DB::beginTransaction();

        try {

            $gamification = Gamification::findOrFail($id);

            $gamification->title = $request->title;
            $gamification->date = $request->date;
            $gamification->student_id = $request->student_id;
            $gamification->class_id = $request->class_id;
            $gamification->section_id = $request->section_id;
            $gamification->note = $request->note;

            if ($request->hasFile('file')) {

                if (
                    $gamification->file &&
                    file_exists(
                        public_path(
                            'uploads/files/gamification/'.$gamification->file
                        )
                    )
                ) {
                    unlink(
                        public_path(
                            'uploads/files/gamification/'.$gamification->file
                        )
                    );
                }

                $file = $request->file('file');

                $filename = time().'_'.$file->getClientOriginalName();

                $file->move(
                    public_path('uploads/files/gamification'),
                    $filename
                );

                $gamification->file = $filename;
            }

            $gamification->save();

            DB::commit();

            Alert::success('Success', 'Gamification updated successfully.');

            return redirect()->route('gamifications.index');
        } catch (Exception $e) {

            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return back();
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $this->service->delete($id);

            DB::commit();
            Alert::toast('Gamification has been delete successfully.', 'success');

            return redirect('gamification');
        } catch (Exception $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());

            return redirect('gamification');
        }
    }
}

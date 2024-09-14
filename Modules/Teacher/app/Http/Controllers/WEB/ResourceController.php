<?php

namespace Modules\Teacher\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Teacher\DataTables\Resource\ResourceDataTable;
use Modules\Teacher\Http\Requests\Resource\ResourceStoreRequest;
use Modules\Teacher\Http\Requests\Resource\ResourceUpdateRequest;
use Modules\Teacher\Models\Resource;
use Modules\Teacher\Services\Resource\ResourceService;
use RealRashid\SweetAlert\Facades\Alert;

class ResourceController extends Controller
{
    public function __construct(
        private ResourceService $service
    ) {}

    public function index(ResourceDataTable $dataTable): mixed
    {
        return $dataTable->render('teacher::resource.index');
    }

    public function create(): Factory|View
    {
        return view('teacher::resource.create');
    }

    public function store(ResourceStoreRequest $request): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $resource = new Resource;
            $resource->institute_id = get_institute_id();
            $resource->branch_id = get_branch_id();
            $resource->session_id = get_option('academic_year');
            $resource->title = $request->title;
            $resource->note = $request->note;
            $resource->date = $request->date;
            $resource->class_id = $request->class_id;
            $resource->section_id = $request->section_id;
            $resource->subject_id = $request->subject_id;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $file_name = secure_public_move($file, 'uploads/files/resources/');
                $resource->file = $file_name;
            }
            if ($request->hasFile('file_2')) {
                $file = $request->file('file_2');
                $file_name = secure_public_move($file, 'uploads/files/resources/');
                $resource->file_2 = $file_name;
            }
            if ($request->hasFile('file_3')) {
                $file = $request->file('file_3');
                $file_name = secure_public_move($file, 'uploads/files/resources/');
                $resource->file_3 = $file_name;
            }
            if ($request->hasFile('file_4')) {
                $file = $request->file('file_4');
                $file_name = secure_public_move($file, 'uploads/files/resources/');
                $resource->file_4 = $file_name;
            }

            $resource->save();
            DB::commit();

            Alert::toast('Resource has been create successfully.', 'success');

            return redirect('resources');
        } catch (Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('resources');
        }
    }

    public function edit(int $id): Factory|View
    {
        $resource = $this->service->getById($id);

        return view('teacher::resource.edit', compact('resource'));
    }

    public function update(ResourceUpdateRequest $request, int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $resource = Resource::find($id);
            if (! $resource) {
                return back()->with('error', _lang('No Data Found!'));
            }
            $resource->institute_id = get_institute_id();
            $resource->branch_id = get_branch_id();
            $resource->session_id = get_option('academic_year');
            $resource->title = $request->title;
            $resource->note = $request->note;
            $resource->date = $request->date;
            $resource->class_id = $request->class_id;
            $resource->section_id = $request->section_id;
            $resource->subject_id = $request->subject_id;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $file_name = secure_public_move($file, 'uploads/files/resources/');
                $resource->file = $file_name;
            }
            if ($request->hasFile('file_2')) {
                $file = $request->file('file_2');
                $file_name = secure_public_move($file, 'uploads/files/resources/');
                $resource->file_2 = $file_name;
            }
            if ($request->hasFile('file_3')) {
                $file = $request->file('file_3');
                $file_name = secure_public_move($file, 'uploads/files/resources/');
                $resource->file_3 = $file_name;
            }
            if ($request->hasFile('file_4')) {
                $file = $request->file('file_4');
                $file_name = secure_public_move($file, 'uploads/files/resources/');
                $resource->file_4 = $file_name;
            }

            $resource->save();

            DB::commit();

            Alert::toast('Resource has been update successfully.', 'success');

            return redirect('resources');
        } catch (Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('resources');
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $this->service->delete($id);

            DB::commit();
            Alert::toast('Resource has been delete successfully.', 'success');

            return redirect('resources');
        } catch (Exception $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());

            return redirect('resources');
        }
    }
}

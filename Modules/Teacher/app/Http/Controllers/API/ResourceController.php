<?php

namespace Modules\Teacher\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Teacher\Http\Requests\Resource\ResourceStoreRequest;
use Modules\Teacher\Http\Requests\Resource\ResourceUpdateRequest;
use Modules\Teacher\Models\Resource;
use Modules\Teacher\Services\Resource\ResourceService;

class ResourceController extends Controller
{
    public function __construct(
        private ResourceService $service
    ) {}

    public function all(Request $request): JsonResponse
    {
        $resources = $this->service->getAll($request);

        if (! $resources) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($resources, 'Resource has been fetch successfully.');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = request('per_page') ?? 10;
        $resources = $this->service->index($request, $perPage);
        if (! $resources) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($resources, 'Resource has been fetch successfully.');
    }

    public function store(ResourceStoreRequest $request): JsonResponse
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

            return $this->responseSuccess([], 'Resource has been create successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Process Error!');
        }
    }

    public function show(int $id): JsonResponse
    {
        $resource = $this->service->getById($id);
        if (! $resource) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($resource, 'Resource has been show successfully.');
    }

    public function update(ResourceUpdateRequest $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $resource = Resource::find($id);
            if (! $resource) {
                return $this->responseError([], _lang('No Data Found!'), 404);
            }
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

            return $this->responseSuccess([], 'Resource has been update successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Process Error!');
        }
    }

    public function destroy(int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $this->service->delete($id);
            if (! $data) {
                return $this->responseError([], 'Data Not Found!', 204);
            }

            DB::commit();

            return $this->responseSuccess($data, 'Resource has been delete successfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Not Found!');
        }
    }
}

<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Picklist;

class APIPicklistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $type = $request->type;
        $query = Picklist::query();

        if ($type) {
            $query->where('type', $type);
        }

        $picklists = $query->select('id', 'type', 'value')->orderBy('id', 'desc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);

        return $this->responseSuccess($picklists, _lang('Picklists has been fetch.'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required',
            'value' => 'required',
        ]);

        // Check if the picklist entry already exists
        $existingPicklist = Picklist::where('type', $request->type)
            ->where('value', $request->value)
            ->first();

        if ($existingPicklist) {
            return $this->responseError([], 'Picklist entry already exists.', 422);
        }

        $picklist = new Picklist;
        $picklist->institute_id = get_institute_id();
        $picklist->branch_id = get_branch_id();
        $picklist->type = $request->type;
        $picklist->value = $request->value;

        $picklist->save();

        return $this->responseSuccess([], _lang('Picklists has been added.'));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $picklist = Picklist::find($id);
        if (! $picklist) {
            return $this->responseError([], _lang('Something went wrong. Picklist can not be found.'), 404);
        }

        return $this->responseSuccess($picklist, _lang('Picklists has been added.'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'type' => 'required',
            'value' => 'required',
        ]);

        $picklist = Picklist::find($id);
        if (! $picklist) {
            return $this->responseError([], _lang('Something went wrong. Picklist can not be found.'), 404);
        }

        $picklist->type = $request->type;
        $picklist->value = $request->value;
        $picklist->save();

        return $this->responseSuccess([], _lang('Picklists has been updated.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $picklist = Picklist::find($id);
        if (! $picklist) {
            return $this->responseError([], _lang('Something went wrong. Picklist can not be found.'), 404);
        }

        $picklist->delete();

        return $this->responseSuccess([], _lang('Picklists has been deleted.'));
    }
}

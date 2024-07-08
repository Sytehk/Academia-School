<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Authentication\Models\Branch;

class BranchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $branches = Branch::where('branches.institute_id', get_institute_id())
            ->select('branches.id', 'branches.institute_id', 'branches.name', 'branches.status')
            ->paginate($perPage);

        return $this->responseSuccess($branches, 'Branches has been fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:191',
        ]);

        $branch = new Branch;
        $branch->institute_id = get_institute_id();
        $branch->name = $request->name;
        $branch->save();

        return $this->responseSuccess([], 'Branch has been create successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $branch = Branch::where('branches.institute_id', get_institute_id())
            ->where('branches.id', $id)
            ->select('branches.id', 'branches.institute_id', 'branches.name', 'branches.status')
            ->first();

        return $this->responseSuccess($branch, 'Branch has been fetch successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:191',
        ]);

        $branch = Branch::where('institute_id', get_institute_id())->find($id);

        if (! $branch) {
            return $this->responseError([], _lang('Something went wrong. branch can not be found.'), 404);
        }

        $branch->name = $request->name;
        $branch->save();

        return $this->responseSuccess($branch, 'Branches has been updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $branch = Branch::where('institute_id', get_institute_id())->find($id);
        if (! $branch) {
            return $this->responseError([], _lang('Something went wrong. branch can not be found.'), 404);
        }

        $branch->delete();

        return $this->responseSuccess([], 'Branches has been deleted successfully.');
    }
}

<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Services\BranchService;
use Modules\Authentication\DataTables\Branch\BranchDataTable;
use Modules\Authentication\Models\Branch;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\User;
use RealRashid\SweetAlert\Facades\Alert;

class BranchController extends Controller
{
    public function __construct(
        private BranchService $service
    ) {}

    public function index(BranchDataTable $dataTable): mixed
    {
        return $dataTable->render('authentication::branch.index');
    }

    public function create(): Factory|View
    {
        $institutes = Institute::all();

        return view('authentication::branch.create', compact('institutes'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $exists = Branch::where('institute_id', auth()->user()->institute_id)
                ->where('name', trim($request->name))
                ->exists();

            if ($exists) {
                Alert::error('Error', 'This branch name already exists.');

                return back()->withInput();
            }

            $this->service->createBranch([
                'name' => $request->name,
                'status' => $request->boolean('status', true),
            ]);

            DB::commit();

            Alert::toast('Branch has been created successfully.', 'success');

            return redirect('branches');
        } catch (\Exception $e) {

            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return back()->withInput();
        }
    }

    public function edit(int $id): Factory|View|RedirectResponse
    {
        $branch = $this->service->findBranchById($id);

        if (! $branch) {
            Alert::error('Error', 'Branch not found.');

            return redirect('branches');
        }

        return view('authentication::branch.edit', compact('branch'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $branch = $this->service->findBranchById($id);

            if (! $branch) {
                DB::rollBack();
                Alert::error('Error', 'Branch not found.');

                return redirect('branches');
            }

            $exists = Branch::where('institute_id', auth()->user()->institute_id)
                ->where('name', trim($request->name))
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                Alert::error('Error', 'This branch name already exists.');

                return back()->withInput();
            }

            $this->service->updateBranch([
                'name' => $request->name,
                'status' => $request->boolean('status'),
            ], $id);

            DB::commit();

            Alert::toast('Branch has been updated successfully.', 'success');

            return redirect('branches');
        } catch (\Exception $e) {

            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return back()->withInput();
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $branch = $this->service->findBranchById($id);

            if (! $branch) {
                DB::rollBack();
                Alert::error('Error', 'Branch not found.');

                return redirect('branches');
            }

            $branchCount = Branch::where('institute_id', $branch->institute_id)->count();
            if ($branchCount <= 1) {
                DB::rollBack();
                Alert::error('Error', 'You cannot delete the only remaining branch of an institute.');

                return redirect('branches');
            }

            $assignedUsers = User::where('branch_id', $id)->count();
            if ($assignedUsers > 0) {
                DB::rollBack();
                Alert::error('Error', "This branch has {$assignedUsers} user(s) assigned. Reassign them before deleting.");

                return redirect('branches');
            }

            $this->service->deleteBranchById($id);

            DB::commit();
            Alert::toast('Branch has been delete successfully.', 'success');

            return redirect('branches');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());

            return redirect('branches');
        }
    }
}

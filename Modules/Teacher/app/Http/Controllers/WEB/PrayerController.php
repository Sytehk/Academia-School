<?php

namespace Modules\Teacher\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\User;
use Modules\Teacher\DataTables\Prayer\PrayerDataTable;
use Modules\Teacher\Http\Requests\Prayer\PrayerStoreRequest;
use Modules\Teacher\Http\Requests\Prayer\PrayerUpdateRequest;
use Modules\Teacher\Models\Prayer;
use Modules\Teacher\Services\Prayer\PrayerService;
use RealRashid\SweetAlert\Facades\Alert;

class PrayerController extends Controller
{
    public function __construct(
        private PrayerService $service
    ) {}

    public function index(PrayerDataTable $dataTable): mixed
    {
        return $dataTable->render('teacher::prayer.index');
    }

    public function create(): Factory|View
    {
        return view('teacher::prayer.create');
    }

    public function store(PrayerStoreRequest $request): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $user = User::where('id', Auth::id())->first();
            $prayer = new Prayer;
            $prayer->institute_id = get_institute_id();
            $prayer->branch_id = get_branch_id();
            $prayer->session_id = get_option('academic_year');
            $prayer->title = $request->title;
            $prayer->note = $request->note;
            $prayer->date = $request->date;
            $prayer->user_id = $user->id;
            $prayer->user_type = $user->user_type;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $file_name = secure_public_move($file, 'uploads/files/prayers/');
                $prayer->file = $file_name;
            }
            $prayer->save();

            DB::commit();

            Alert::toast('Prayer has been create successfully.', 'success');

            return redirect('prayers');
        } catch (Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('prayers');
        }
    }

    public function edit(int $id): Factory|View
    {
        $prayer = $this->service->getById($id);

        return view('teacher::prayer.edit', compact('prayer'));
    }

    public function update(PrayerUpdateRequest $request, int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $prayer = Prayer::find($id);
            if (! $prayer) {
                return back()->with('error', _lang('No Data Found!'));
            }
            $prayer->institute_id = get_institute_id();
            $prayer->branch_id = get_branch_id();
            $prayer->session_id = get_option('academic_year');
            $prayer->title = $request->title;
            $prayer->note = $request->note;
            $prayer->date = $request->date;
            $prayer->user_id = Auth::id();

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $file_name = secure_public_move($file, 'uploads/files/prayers/');
                $prayer->file = $file_name;
            }
            $prayer->save();

            DB::commit();
            Alert::toast('Prayer has been update successfully.', 'success');

            return redirect('prayers');
        } catch (Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('prayers');
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $this->service->delete($id);

            DB::commit();
            Alert::toast('Prayer has been delete successfully.', 'success');

            return redirect('prayers');
        } catch (Exception $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());

            return redirect('prayers');
        }
    }
}

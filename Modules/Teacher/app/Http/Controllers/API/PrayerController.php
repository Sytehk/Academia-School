<?php

namespace Modules\Teacher\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\User;
use Modules\Teacher\Http\Requests\Prayer\PrayerStoreRequest;
use Modules\Teacher\Http\Requests\Prayer\PrayerUpdateRequest;
use Modules\Teacher\Models\Prayer;
use Modules\Teacher\Services\Prayer\PrayerService;

class PrayerController extends Controller
{
    public function __construct(
        private PrayerService $service
    ) {}

    public function all(Request $request): JsonResponse
    {
        $prayers = $this->service->getAll($request);

        if (! $prayers) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($prayers, 'Prayer has been fetch successfully.');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = request('per_page') ?? 10;
        $prayers = $this->service->index($request, $perPage);
        if (! $prayers) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($prayers, 'Prayer has been fetch successfully.');
    }

    public function store(PrayerStoreRequest $request): JsonResponse
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

            return $this->responseSuccess([], 'Prayer has been create successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Process Error!');
        }
    }

    public function show(int $id): JsonResponse
    {
        $prayer = $this->service->getById($id);
        if (! $prayer) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($prayer, 'Prayer has been show successfully.');
    }

    public function update(PrayerUpdateRequest $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $prayer = Prayer::find($id);
            if (! $prayer) {
                return $this->responseError([], _lang('No Data Found!'), 404);
            }
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

            return $this->responseSuccess([], 'Prayer has been update successfully.');
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

            return $this->responseSuccess($data, 'Prayer has been delete successfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Not Found!');
        }
    }
}

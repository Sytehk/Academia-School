<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Modules\Academic\Models\Notice;
use Modules\Academic\Models\UserNotice;

class APINoticeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $notices = Notice::select('id', 'title', 'notice', 'date', 'image', 'created_by')->with('userType')->paginate($perPage);

        return $this->responseSuccess($notices, 'Notices fetch successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string',
            'notice' => 'required|string',
            'user_type' => 'required',
            'image' => 'nullable|image|max:2048', // Ensure image validation
        ]);

        $userTypes = is_string($request->user_type) ? json_decode($request->user_type, true) : $request->user_type;
        if (! is_array($userTypes)) {
            return response()->json(['error' => 'Invalid user type format'], 422);
        }

        $notice = new Notice;
        $notice->institute_id = get_institute_id();
        $notice->branch_id = get_branch_id();
        $notice->title = $request->title;
        $notice->notice = $request->notice;
        $notice->date = Carbon::now();
        $notice->created_by = Auth::id();
        if (! empty($request['image']) && $request['image'] instanceof UploadedFile) {
            $notice->image = fileUploader('notices/', 'png', $request['image']);
        }
        $notice->save();

        foreach ($userTypes as $user_type) {
            UserNotice::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'notice_id' => $notice->id,
                'user_type' => $user_type,
            ]);
        }

        return $this->responseSuccess([], _lang('Notice has been create successfully.'));
    }

    public function show(int $id): JsonResponse
    {
        $notice = Notice::find($id);
        if (! $notice) {
            return $this->responseError([], _lang('Something went wrong. Notice can not be found.'), 404);
        }

        return $this->responseSuccess($notice, _lang('Notice has been fetch.'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'required',
            'notice' => 'required',
            'image' => 'nullable',
        ]);

        $notice = Notice::find($id);
        if (! $notice) {
            return $this->responseError([], _lang('Something went wrong. Notice can not be found.'), 404);
        }
        $notice->title = $request->title;
        $notice->notice = $request->notice;
        if (! empty($request['image']) && $request['image'] instanceof UploadedFile) {
            $notice->image = fileUploader('notices/', 'png', $request['image'], $notice->image);
        }
        $notice->save();

        $userNotice = UserNotice::where('notice_id', $id);
        if (! $userNotice) {
            return $this->responseError([], _lang('Something went wrong. User Notice can not be found.'), 404);
        }
        $userNotice->delete();

        foreach ($request->user_type as $user_type) {
            $userNotice = new UserNotice;
            $userNotice->institute_id = get_institute_id();
            $userNotice->branch_id = get_branch_id();
            $userNotice->notice_id = $notice->id;
            $userNotice->user_type = $user_type;
            $userNotice->save();
        }

        return $this->responseSuccess([], _lang('Notice has been updated successfully.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $notice = Notice::find($id);
        if (! $notice) {
            return $this->responseError([], _lang('Something went wrong. Notice can not be found.'), 404);
        }

        $notice->delete();

        return $this->responseSuccess([], _lang('Notice has been deleted successfully.'));
    }
}

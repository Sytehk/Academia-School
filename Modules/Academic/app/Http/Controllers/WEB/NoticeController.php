<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Notice;
use Modules\Academic\Models\UserNotice;
use RealRashid\SweetAlert\Facades\Alert;

class NoticeController extends Controller
{
    public function index(): Factory|View
    {
        $notices = Notice::all()->sortByDesc('id');

        return view('academic::notice.list', compact('notices'));
    }

    public function create(Request $request): Factory|View
    {
        $roles = DB::table('roles')->get();

        return view('academic::notice.create', compact('roles'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'title' => 'required|string',
            'notice' => 'required|string',
            'user_type' => 'required',
            'image' => 'nullable|image|max:2048', // Ensure image validation
        ]);

        $userTypes = is_string($request->user_type) ? json_decode($request->user_type, true) : $request->user_type;
        if (! is_array($userTypes)) {
            return back();
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

        Alert::toast('Notice has been create successfully.', 'success');

        return redirect('notices');
    }

    public function show(Request $request, int $id): Factory|View
    {
        $notice = Notice::find($id);

        return view('academic::notice.view', compact('notice', 'id'));
    }

    public function edit(Request $request, int $id): Factory|View
    {
        $notice = Notice::find($id);
        $roles = DB::table('roles')->get();

        return view('academic::notice.edit', compact('notice', 'id', 'roles'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'title' => 'required',
            'notice' => 'required',
            'image' => 'nullable',
        ]);

        $notice = Notice::find($id);
        if (! $notice) {
            return back();
        }
        $notice->title = $request->title;
        $notice->notice = $request->notice;
        if (! empty($request['image']) && $request['image'] instanceof UploadedFile) {
            $notice->image = fileUploader('notices/', 'png', $request['image'], $notice->image);
        }
        $notice->save();

        $userNotice = UserNotice::where('notice_id', $id);
        if (! $userNotice) {
            return back();
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

        Alert::toast('Notice has been update successfully.', 'success');

        return redirect('notices');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $notice = Notice::find($id);
        $notice->delete();

        Alert::toast('Notice has been delete successfully.', 'success');

        return redirect('notices');
    }
}

<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Hostel\Models\HostelLeave;
use Modules\Hostel\Models\RoomMember;
use RealRashid\SweetAlert\Facades\Alert;

class HostelLeaveController extends Controller
{
    use HasPermissionWebTrait;

    public function index(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_leave_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $leaves = HostelLeave::with(['student.user', 'roomMember.room'])->orderByDesc('id')->paginate(20);

        return view('hostel::hostel_leave.index', compact('leaves'));
    }

    public function create(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_leave_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $roomMembers = RoomMember::with('student.user')->where('status', 'active')->get();

        return view('hostel::hostel_leave.create', compact('roomMembers'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('hostel_leave_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $data = $request->validate([
            'room_member_id' => 'required|integer|exists:room_members,id',
            'leave_start' => 'required|date',
            'leave_end' => 'required|date|after_or_equal:leave_start',
            'reason' => 'nullable|string|max:500',
        ]);

        $roomMember = RoomMember::findOrFail($data['room_member_id']);

        HostelLeave::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'student_id' => $roomMember->student_id,
            'room_member_id' => $roomMember->id,
            'leave_start' => $data['leave_start'],
            'leave_end' => $data['leave_end'],
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        Alert::toast('Leave request created successfully.', 'success');

        return redirect()->route('hostel-leaves.index');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $permissionResult = $this->checkPermission('hostel_leave_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $request->validate(['status' => 'required|in:approved,rejected']);

        HostelLeave::findOrFail($id)->update(['status' => $request->status]);

        Alert::toast('Leave status updated successfully.', 'success');

        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $permissionResult = $this->checkPermission('hostel_leave_delete');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        HostelLeave::findOrFail($id)->delete();

        Alert::toast('Leave request deleted successfully.', 'success');

        return back();
    }
}

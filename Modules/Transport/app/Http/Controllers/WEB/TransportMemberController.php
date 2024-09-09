<?php

namespace Modules\Transport\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Academic\Models\Student;
use Modules\Transport\Http\Requests\TransportMember\TransportMemberStoreRequest;
use Modules\Transport\Http\Requests\TransportMember\TransportMemberUpdateRequest;
use Modules\Transport\Models\BusRoute;
use Modules\Transport\Models\BusStop;
use Modules\Transport\Models\TransportMember;
use Modules\Transport\Repositories\TransportMemberRepository;

class TransportMemberController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private TransportMemberRepository $member
    ) {}

    public function index()
    {
        try {

            $members = $this->member->getAll(
                request()->all()
            );

            return view('transport::transport_member.index', compact('members'));
        } catch (Exception $exception) {

            return back()->with(
                'error',
                $exception->getMessage()
            );
        }
    }

    public function create()
    {

        $students = Student::select(
            'id',
            'first_name',
            'last_name'
        )
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->get();

        $routes = BusRoute::select(
            'id',
            'route_name'
        )->get();

        $stops = BusStop::select(
            'id',
            'stop_name'
        )->get();

        return view(
            'transport::transport_member.create',
            compact(
                'students',
                'routes',
                'stops'
            )
        );
    }

    public function store(
        TransportMemberStoreRequest $request
    ) {

        try {

            $this->member->create(
                $request->validated()
            );

            return redirect()
                ->route('transport-members.index')
                ->with(
                    'success',
                    'Transport Member created successfully.'
                );
        } catch (Exception $exception) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }

    public function edit(int $id)
    {

        $member = $this->member->getById($id);

        if (! $member) {

            return redirect()
                ->route('transport-members.index')
                ->with(
                    'error',
                    'Member not found.'
                );
        }

        $students = Student::select(
            'id',
            'first_name',
            'last_name'
        )
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->get();

        $routes = BusRoute::select(
            'id',
            'route_name'
        )->get();

        $stops = BusStop::select(
            'id',
            'stop_name'
        )->get();

        return view(
            'transport::transport_member.edit',
            compact(
                'member',
                'students',
                'routes',
                'stops'
            )
        );
    }

    public function update(
        TransportMemberUpdateRequest $request,
        int $id
    ) {

        try {

            $this->member->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('transport-members.index')
                ->with(
                    'success',
                    'Transport Member updated successfully.'
                );
        } catch (Exception $exception) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }

    public function destroy(int $id)
    {

        $this->member->delete($id);

        return redirect()
            ->route('transport-members.index')
            ->with(
                'success',
                'Deleted successfully'
            );
    }

    public function card(int $id)
    {
        $member = TransportMember::with(['student.user', 'route', 'stop'])->findOrFail($id);

        return view('transport::transport_member.card', compact('member'));
    }
}

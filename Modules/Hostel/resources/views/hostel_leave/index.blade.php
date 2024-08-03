@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Hostel Leave Requests') }}
@endsection

@section('create-button')
    @can('hostel_leave_create')
        <a href="{{ route('hostel-leaves.create') }}" class="btn btn-primary">{{ _lang('Add Leave Request') }}</a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ _lang('Student') }}</th>
                        <th>{{ _lang('Room') }}</th>
                        <th>{{ _lang('From') }}</th>
                        <th>{{ _lang('To') }}</th>
                        <th>{{ _lang('Reason') }}</th>
                        <th>{{ _lang('Status') }}</th>
                        <th>{{ _lang('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaves as $leave)
                        <tr>
                            <td>{{ $leave->id }}</td>
                            <td>{{ $leave->student?->user?->name ?? '-' }}</td>
                            <td>{{ $leave->roomMember?->room?->room_number ?? '-' }}</td>
                            <td>{{ $leave->leave_start }}</td>
                            <td>{{ $leave->leave_end }}</td>
                            <td>{{ $leave->reason }}</td>
                            <td>
                                @php
                                    $badge = match ($leave->status) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'warning',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($leave->status) }}</span>
                            </td>
                            <td>
                                @can('hostel_leave_update')
                                    @if ($leave->status === 'pending')
                                        <form action="{{ route('hostel-leaves.status', $leave->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button class="btn btn-sm btn-success">{{ _lang('Approve') }}</button>
                                        </form>
                                        <form action="{{ route('hostel-leaves.status', $leave->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button class="btn btn-sm btn-danger">{{ _lang('Reject') }}</button>
                                        </form>
                                    @endif
                                @endcan
                                @can('hostel_leave_delete')
                                    <form action="{{ route('hostel-leaves.destroy', $leave->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')">{{ _lang('Delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">{{ _lang('No leave requests found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $leaves->links() }}
        </div>
    </div>
@endsection

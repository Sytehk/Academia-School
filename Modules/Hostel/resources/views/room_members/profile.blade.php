@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Student Hostel Profile') }}
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <h5>{{ $member->student?->user?->name ?? '-' }}</h5>
            <div class="row">
                <div class="col-md-3"><strong>{{ _lang('Hostel') }}:</strong> {{ $member->room?->hostel?->hostel_name ?? '-' }}</div>
                <div class="col-md-3"><strong>{{ _lang('Building') }}:</strong> {{ $member->room?->building?->building_name ?? '-' }}</div>
                <div class="col-md-3"><strong>{{ _lang('Floor') }}:</strong> {{ $member->room?->floor?->floor_name ?? '-' }}</div>
                <div class="col-md-3"><strong>{{ _lang('Room') }}:</strong> {{ $member->room?->room_number ?? '-' }}</div>
                <div class="col-md-3 mt-2"><strong>{{ _lang('Bed') }}:</strong> {{ $member->bed?->bed_number ?? '-' }}</div>
                <div class="col-md-3 mt-2"><strong>{{ _lang('Start Date') }}:</strong> {{ $member->start_date }}</div>
                <div class="col-md-3 mt-2"><strong>{{ _lang('Status') }}:</strong>
                    <span class="badge bg-{{ $member->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($member->status) }}</span>
                </div>
                <div class="col-md-3 mt-2">
                    <a href="{{ route('room-members.card', $member->id) }}" class="btn btn-sm btn-dark" target="_blank">{{ _lang('ID Card') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">{{ _lang('Fee Collection History') }}</div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>{{ _lang('Invoice') }}</th>
                        <th>{{ _lang('Month') }}</th>
                        <th>{{ _lang('Paid') }}</th>
                        <th>{{ _lang('Due') }}</th>
                        <th>{{ _lang('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($member->collections as $collection)
                        <tr>
                            <td>{{ $collection->invoice_id }}</td>
                            <td>{{ $collection->month ?? '-' }}</td>
                            <td>{{ $collection->total_paid }}</td>
                            <td>{{ $collection->total_due }}</td>
                            <td>{{ $collection->invoice_date }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">{{ _lang('No collections yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">{{ _lang('Leave History') }}</div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>{{ _lang('From') }}</th>
                        <th>{{ _lang('To') }}</th>
                        <th>{{ _lang('Reason') }}</th>
                        <th>{{ _lang('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($member->leaves as $leave)
                        <tr>
                            <td>{{ $leave->leave_start }}</td>
                            <td>{{ $leave->leave_end }}</td>
                            <td>{{ $leave->reason }}</td>
                            <td>{{ ucfirst($leave->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">{{ _lang('No leave records.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

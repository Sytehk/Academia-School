@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('My Hostel') }}
@endsection

@section('content')
    @if ($roomMember)
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><strong>{{ _lang('Hostel') }}:</strong> {{ $roomMember->room?->hostel?->hostel_name ?? '-' }}</div>
                    <div class="col-md-3"><strong>{{ _lang('Building') }}:</strong> {{ $roomMember->room?->building?->building_name ?? '-' }}</div>
                    <div class="col-md-3"><strong>{{ _lang('Floor') }}:</strong> {{ $roomMember->room?->floor?->floor_name ?? '-' }}</div>
                    <div class="col-md-3"><strong>{{ _lang('Room') }}:</strong> {{ $roomMember->room?->room_number ?? '-' }}</div>
                    <div class="col-md-3 mt-2"><strong>{{ _lang('Bed') }}:</strong> {{ $roomMember->bed?->bed_number ?? '-' }}</div>
                    <div class="col-md-3 mt-2"><strong>{{ _lang('Since') }}:</strong> {{ $roomMember->start_date }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">{{ _lang('Fee History') }}</div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>{{ _lang('Invoice') }}</th>
                            <th>{{ _lang('Month') }}</th>
                            <th>{{ _lang('Paid') }}</th>
                            <th>{{ _lang('Due') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roomMember->collections as $collection)
                            <tr>
                                <td>{{ $collection->invoice_id }}</td>
                                <td>{{ $collection->month ?? '-' }}</td>
                                <td>{{ $collection->total_paid }}</td>
                                <td>{{ $collection->total_due }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">{{ _lang('No collections yet.') }}</td></tr>
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
                            <th>{{ _lang('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roomMember->leaves as $leave)
                            <tr>
                                <td>{{ $leave->leave_start }}</td>
                                <td>{{ $leave->leave_end }}</td>
                                <td>{{ ucfirst($leave->status) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center">{{ _lang('No leave records.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-info">{{ _lang('You are not currently assigned to a hostel room.') }}</div>
    @endif
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Google Meet') }}
@endsection

@section('create-button')
    @can('google_meet_create')
        <a href="{{ route('google-meet.create') }}" class="btn btn-primary">
            {{ _lang('Create Meeting') }}
        </a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <div class="card-body">

            <form method="GET" action="{{ route('google-meet.index') }}" class="row g-3 mb-4">
                <div class="col-md-2">
                    <select name="class_id" class="form-control">
                        <option value="">{{ _lang('All Classes') }}</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="section_id" class="form-control"
                        data-selected="{{ request('section_id') }}">
                        <option value="">{{ _lang('Select Class First') }}</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">{{ _lang('All Status') }}</option>
                        <option value="scheduled" @selected(request('status') == 'scheduled')>{{ _lang('Scheduled') }}</option>
                        <option value="completed" @selected(request('status') == 'completed')>{{ _lang('Completed') }}</option>
                        <option value="cancelled" @selected(request('status') == 'cancelled')>{{ _lang('Cancelled') }}</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>

                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="{{ _lang('Search title') }}" value="{{ request('search') }}">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary w-100">{{ _lang('Filter') }}</button>
                </div>
            </form>

            <div class="d-flex justify-content-end gap-2 mb-3">
                @can('google_meet_export')
                    <a href="{{ route('google-meet.export-pdf', request()->query()) }}" class="btn btn-sm btn-secondary">{{ _lang('Export PDF') }}</a>
                    <a href="{{ route('google-meet.export-excel', request()->query()) }}" class="btn btn-sm btn-secondary">{{ _lang('Export Excel') }}</a>
                @endcan
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Teacher') }}</th>
                            <th>{{ _lang('Class') }}</th>
                            <th>{{ _lang('Section') }}</th>
                            <th>{{ _lang('Date') }}</th>
                            <th>{{ _lang('Time') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Meet Link') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($meetings as $meeting)
                            <tr>
                                <td>{{ $meeting->id }}</td>
                                <td>{{ $meeting->title }}</td>
                                <td>{{ $meeting->teacher?->name ?? '-' }}</td>
                                <td>{{ $meeting->classModel?->class_name ?? '-' }}</td>
                                <td>{{ $meeting->section?->section_name ?? '-' }}</td>
                                <td>{{ optional($meeting->start_date)->format('d M Y') }}</td>
                                <td>{{ $meeting->start_time }} - {{ $meeting->end_time }}</td>
                                <td>
                                    @php
                                        $badge = match ($meeting->computedStatus()) {
                                            'Live Now' => 'success',
                                            'Cancelled' => 'danger',
                                            'Completed' => 'secondary',
                                            default => 'info',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ $meeting->computedStatus() }}</span>
                                </td>
                                <td>
                                    @if ($meeting->meet_link && $meeting->status !== 'cancelled')
                                        <a href="{{ $meeting->meet_link }}" target="_blank" class="btn btn-sm btn-success">{{ _lang('Join') }}</a>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        @can('google_meet_update')
                                            <a href="{{ route('google-meet.edit', $meeting->id) }}" class="btn btn-sm btn-warning">{{ _lang('Edit') }}</a>
                                        @endcan

                                        @can('google_meet_duplicate')
                                            <form action="{{ route('google-meet.duplicate', $meeting->id) }}" method="POST">
                                                @csrf
                                                <button class="btn btn-sm btn-info">{{ _lang('Duplicate') }}</button>
                                            </form>
                                        @endcan

                                        @can('google_meet_cancel')
                                            @if ($meeting->status !== 'cancelled')
                                                <form action="{{ route('google-meet.cancel', $meeting->id) }}" method="POST">
                                                    @csrf
                                                    <button class="btn btn-sm btn-secondary" onclick="return confirm('Cancel this meeting?')">{{ _lang('Cancel') }}</button>
                                                </form>
                                            @endif
                                        @endcan

                                        <a href="{{ route('google-meet.print', $meeting->id) }}" class="btn btn-sm btn-dark" target="_blank">{{ _lang('Print') }}</a>

                                        @can('google_meet_delete')
                                            <form action="{{ route('google-meet.destroy', $meeting->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this meeting?')">{{ _lang('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">{{ _lang('No meetings found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $meetings->links() }}
        </div>
    </div>
@endsection

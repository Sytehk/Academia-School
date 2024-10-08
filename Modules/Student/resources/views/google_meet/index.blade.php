@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Google Meet') }}
@endsection

@section('content')
    <div class="row g-4 mb-4">
        @php
            $upcoming = $meetings->where('status', '!=', 'cancelled')->filter(fn($m) => $m->computedStatus() === 'Upcoming')->count();
            $live = $meetings->where('status', '!=', 'cancelled')->filter(fn($m) => $m->computedStatus() === 'Live Now')->count();
            $completed = $meetings->filter(fn($m) => $m->computedStatus() === 'Completed')->count();
        @endphp
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0">{{ $upcoming }}</h3>
                    <span class="text-muted">{{ _lang('Upcoming') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="mb-0 text-success">{{ $live }}</h3>
                    <span class="text-muted">{{ _lang('Live Now') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0">{{ $completed }}</h3>
                    <span class="text-muted">{{ _lang('Completed') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Teacher') }}</th>
                            <th>{{ _lang('Subject') }}</th>
                            <th>{{ _lang('Date & Time') }}</th>
                            <th>{{ _lang('Duration') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Join') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($meetings as $key => $meeting)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    {{ $meeting->title }}
                                    @if ($meeting->description)
                                        <br><small class="text-muted">{{ $meeting->description }}</small>
                                    @endif
                                </td>
                                <td>{{ $meeting->teacher?->name ?? 'N/A' }}</td>
                                <td>{{ $meeting->subject?->subject_name ?? 'N/A' }}</td>
                                <td>{{ optional($meeting->start_date)->format('d M Y') }} {{ $meeting->start_time }}</td>
                                <td>{{ $meeting->duration }} {{ _lang('min') }}</td>
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
                                    @if ($meeting->meet_link && $meeting->computedStatus() === 'Live Now')
                                        <a href="{{ $meeting->meet_link }}" target="_blank" class="btn btn-sm btn-success">{{ _lang('Join Now') }}</a>
                                    @elseif ($meeting->meet_link && $meeting->computedStatus() === 'Upcoming')
                                        <span class="text-muted">{{ _lang('Not started') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">{{ _lang('No Google Meet sessions available.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

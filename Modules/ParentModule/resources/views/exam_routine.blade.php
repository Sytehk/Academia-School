@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Exam Routine') }}
@endsection

@section('content')
    @include('parentmodule::partials.child_context')

    @forelse ($exams as $group)
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title">{{ $group['exam']->name ?? _lang('Exam') }}</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-dashed gy-2 align-middle">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4">{{ _lang('Subject') }}</th>
                                <th>{{ _lang('Code') }}</th>
                                <th>{{ _lang('Date') }}</th>
                                <th>{{ _lang('Time') }}</th>
                                <th class="pe-4">{{ _lang('Room') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group['subjects'] as $subject)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $subject['subject_name'] }}</td>
                                    <td>{{ $subject['subject_code'] ?: '—' }}</td>
                                    <td>{{ $subject['date'] ? \Carbon\Carbon::parse($subject['date'])->format('d M, Y') : '—' }}</td>
                                    <td>
                                        @if ($subject['start_time'])
                                            {{ $subject['start_time'] }} - {{ $subject['end_time'] }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="pe-4">{{ $subject['room'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-muted text-center py-10">{{ _lang('No exam routine found.') }}</div>
        </div>
    @endforelse
@endsection

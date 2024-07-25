@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Online Examinations') }}
@endsection
@section('content')
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ _lang('Exam') }}</th>
                        <th>{{ _lang('Subject') }}</th>
                        <th>{{ _lang('Window') }}</th>
                        <th>{{ _lang('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exams as $exam)
                        @php $studentAttempts = $attempts->get($exam->id, collect()); $submitted = $studentAttempts->first(fn ($row) => $row->isSubmitted()); @endphp
                        <tr>
                            <td>{{ $exam->name }}</td>
                            <td>{{ $exam->subject?->subject_name }}</td>
                            <td>{{ optional($exam->start_at)->format('d M H:i') }} - {{ optional($exam->end_at)->format('d M H:i') }}</td>
                            <td>{{ $submitted ? _lang('Submitted') : ($exam->isOpen() ? _lang('Open') : _lang('Closed')) }}</td>
                            <td>
                                @if ($submitted)
                                    <a class="btn btn-sm btn-light" href="{{ route('student.online-exams.result', $submitted->id) }}">{{ _lang('View Result') }}</a>
                                @elseif ($exam->isOpen())
                                    <form method="POST" action="{{ route('student.online-exams.start', $exam->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">{{ _lang('Start Exam') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">{{ _lang('No online exams available.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

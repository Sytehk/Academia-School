@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Online Exam Result') }}
@endsection
@section('content')
    <div class="card">
        <div class="card-header"><h3 class="card-title">{{ $attempt->onlineExam->name }}</h3></div>
        <div class="card-body">
            @if (! $attempt->onlineExam->resultVisible())
                <p>{{ _lang('Your answers were submitted. The result will be released by your teacher.') }}</p>
            @else
                <p>{{ _lang('Score') }}: <strong>{{ $attempt->score }}</strong> / {{ $attempt->max_score }}</p>
                <p>{{ _lang('Converted') }}: {{ $attempt->converted_score }}</p>
                <p>{{ _lang('Correct') }}: {{ $attempt->correct_count }} | {{ _lang('Wrong') }}: {{ $attempt->incorrect_count }} | {{ _lang('Skipped') }}: {{ $attempt->skipped_count }}</p>
                @if ($attempt->grading_status === \Modules\Examination\Models\OnlineExamAttempt::GRADING_PENDING)
                    <p class="text-warning">{{ _lang('Some answers are waiting to be marked by your teacher. Your final score may change.') }}</p>
                @else
                    <p>{{ _lang('Result') }}: {{ $attempt->is_passed ? _lang('Pass') : _lang('Fail') }}</p>
                @endif
            @endif
            <p>{{ _lang('Status') }}: {{ $attempt->status }}</p>
        </div>
    </div>
@endsection

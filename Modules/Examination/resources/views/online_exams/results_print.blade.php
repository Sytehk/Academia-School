@extends('examination::results.print_layout')
@section('content')
    @include('examination::results.partials.school_header', ['header' => $header ?? []])
    <div class="doc-title">{{ _lang('Online Exam Result') }} — {{ $onlineExam->name }}</div>
    <table class="marks-table">
        <thead>
            <tr>
                <th>{{ _lang('Student') }}</th>
                <th>{{ _lang('Score') }}</th>
                <th>{{ _lang('Converted') }}</th>
                <th>{{ _lang('Correct') }}</th>
                <th>{{ _lang('Wrong') }}</th>
                <th>{{ _lang('Result') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attempts as $attempt)
                <tr>
                    <td class="left">{{ $attempt->student?->first_name }} {{ $attempt->student?->last_name }}</td>
                    <td>{{ $attempt->score }}</td>
                    <td>{{ $attempt->converted_score }}</td>
                    <td>{{ $attempt->correct_count }}</td>
                    <td>{{ $attempt->incorrect_count }}</td>
                    <td>{{ $attempt->is_passed ? _lang('Pass') : _lang('Fail') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

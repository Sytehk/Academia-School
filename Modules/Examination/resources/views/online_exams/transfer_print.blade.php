@extends('examination::results.print_layout')
@section('content')
    @include('examination::results.partials.school_header', ['header' => $header ?? []])
    <div class="doc-title">{{ _lang('Online Exam Mark Transfer Preview') }}</div>
    <table class="marks-table">
        <thead>
            <tr>
                <th>{{ _lang('Student') }}</th>
                <th>{{ _lang('Subject') }}</th>
                <th>{{ _lang('Online Score') }}</th>
                <th>{{ _lang('Mark Input') }}</th>
                <th>{{ _lang('Existing') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($preview as $row)
                <tr>
                    <td class="left">{{ $row['student_name'] }}</td>
                    <td>{{ $row['subject_name'] }}</td>
                    <td>{{ $row['online_score'] }} / {{ $row['online_full_marks'] }}</td>
                    <td>{{ $row['transferred_marks'] }} / {{ $row['academic_full_marks'] }}</td>
                    <td>{{ $row['existing_mark'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

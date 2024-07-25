@extends('examination::results.print_layout')

@section('extra_css')
    <style>
        .empty-cell { height: 22px; min-width: 48px; }
        .sign-row td { height: 60px; text-align: center; vertical-align: bottom; }
        @page { size: landscape; margin: 10mm; }
    </style>
@endsection

@section('content')
    @include('examination::results.partials.school_header', ['header' => $sheet['header'] ?? []])
    <div class="doc-title">{{ _lang('Manual Mark Input Sheet') }} — {{ $sheet['exam_name'] ?? '' }}</div>
    <table class="info-table">
        <tr>
            <td><strong>{{ _lang('Class') }}:</strong> {{ $sheet['class_name'] ?? '' }}</td>
            <td><strong>{{ _lang('Section') }}:</strong> {{ $sheet['section_name'] ?? '' }}</td>
            <td><strong>{{ _lang('Group') }}:</strong> {{ $sheet['group_name'] ?? '' }}</td>
            <td><strong>{{ _lang('Session') }}:</strong> {{ $sheet['session_name'] ?? '' }}</td>
        </tr>
    </table>
    <table class="marks-table">
        <thead>
            <tr>
                <th>{{ _lang('SL') }}</th>
                <th>{{ _lang('Student') }}</th>
                <th>{{ _lang('Roll') }}</th>
                @foreach ($sheet['subjects'] as $subject)
                    <th>{{ $subject['name'] }}</th>
                @endforeach
                <th>{{ _lang('Signature') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sheet['students'] as $student)
                <tr>
                    <td>{{ $student['sl'] }}</td>
                    <td class="left">{{ $student['student_name'] }}</td>
                    <td>{{ $student['roll'] }}</td>
                    @foreach ($sheet['subjects'] as $subject)
                        <td class="empty-cell"></td>
                    @endforeach
                    <td class="empty-cell"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table class="marks-table sign-row">
        <tr>
            <td>{{ _lang('Teacher Signature') }}</td>
            <td>{{ _lang('Examiner Signature') }}</td>
            <td>{{ _lang('Head Teacher Signature') }}</td>
            <td>{{ _lang('Date') }}: _______________</td>
        </tr>
    </table>
@endsection

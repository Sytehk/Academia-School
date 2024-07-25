@extends('examination::results.print_layout')

@php
    $header = $header ?? [];
    $record = $record ?? [];
    $student = $record['student'] ?? [];
    $sessions = $record['sessions'] ?? [];
@endphp

@section('content')
    <div class="result-card">
        @include('examination::results.partials.school_header', ['header' => $header])
        <div class="doc-title">{{ _lang('Academic Transcript') }}</div>

        <table class="info-table">
            <tr>
                <td><strong>{{ _lang('Name') }}:</strong> {{ $student['name'] ?? '' }}</td>
                <td><strong>{{ _lang('Registration No') }}:</strong> {{ $student['registration_no'] ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>{{ _lang('Date of Birth') }}:</strong> {{ $student['birthday'] ?? '-' }}</td>
                <td><strong>{{ _lang('Transcript No') }}:</strong> {{ $issue->transcript_number ?? '-' }}</td>
            </tr>
        </table>

        @foreach ($sessions as $session)
            <div class="doc-title" style="font-size:16px; margin-top:18px;">
                {{ $session['session_name'] }} — {{ $session['class_name'] }} {{ $session['section_name'] }}
                @if ($session['roll']) ({{ _lang('Roll') }} {{ $session['roll'] }}) @endif
            </div>

            @foreach ($session['terms'] as $term)
                <table class="marks-table" style="margin-bottom:10px;">
                    <thead>
                        <tr>
                            <th colspan="5" class="left">{{ $term['exam_name'] }}</th>
                        </tr>
                        <tr>
                            <th>{{ _lang('Subject') }}</th>
                            <th>{{ _lang('Full Marks') }}</th>
                            <th>{{ _lang('Obtained') }}</th>
                            <th>{{ _lang('Grade') }}</th>
                            <th>{{ _lang('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($term['subjects'] as $subject)
                            <tr>
                                <td class="left">{{ $subject['subject_name'] }}</td>
                                <td>{{ $subject['full_marks'] }}</td>
                                <td>{{ $subject['obtained'] ?? '-' }}</td>
                                <td>{{ $subject['grade'] ?? '-' }}</td>
                                <td>{{ strtoupper($subject['status'] ?? '-') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p>{{ _lang('Average') }}: {{ $term['average'] }}% &nbsp; {{ _lang('Grade') }}: {{ $term['grade'] }}
                    &nbsp; {{ _lang('Position') }}: {{ $term['position'] ?? '-' }} / {{ $term['students_in_class'] }}</p>
            @endforeach

            @if ($session['annual'] ?? null)
                <p><strong>{{ _lang('Annual Result') }}:</strong>
                    {{ _lang('Grade') }} {{ $session['annual']['grade'] }}
                    ({{ $session['annual']['final_score'] ?? $session['annual']['final_obtained'] }})
                </p>
            @endif
        @endforeach

        @if (!empty($record['promotions']))
            <div class="doc-title" style="font-size:16px; margin-top:18px;">{{ _lang('Promotion History') }}</div>
            <ul>
                @foreach ($record['promotions'] as $promotion)
                    <li>{{ ucfirst($promotion['action']) }} — {{ $promotion['date'] }}</li>
                @endforeach
            </ul>
        @endif

        <table class="summary-table" style="margin-top:16px;">
            <tr>
                <td>{{ _lang('Overall Average') }}: <span class="accent">{{ $record['overall_average'] ?? '-' }}%</span></td>
            </tr>
        </table>

        <div class="result-footer">
            {{ _lang('Verification code') }}: {{ $issue->verification_code ?? '' }} —
            {{ _lang('This transcript is valid with official institute seal and signature.') }}
        </div>
    </div>
@endsection

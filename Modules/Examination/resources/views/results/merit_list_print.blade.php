@extends('examination::results.print_layout')

@php
    $header = $sheet['header'] ?? [];
    $theme = $header['theme'] ?? [];
    $primary = $theme['primary_color'] ?? '#1a365d';
@endphp

@section('extra_css')
    <style>
        .merit-table { width: 100%; border-collapse: collapse; }
        .merit-table th, .merit-table td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: center;
            font-size: 12px;
        }
        .merit-table th {
            background: {{ $primary }};
            color: #fff;
            font-weight: 700;
        }
        .merit-table tbody tr:nth-child(even) td { background: #f9fbfd; }
        .merit-table td.name { text-align: left; }
        .merit-table td.position { font-weight: 700; }
        .exam-meta { width: 100%; margin-bottom: 8px; font-size: 11px; border-collapse: collapse; }
        .exam-meta td { padding: 3px 6px; border: 1px solid #ddd; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>
@endsection

@section('content')
    <div class="sheet-wrap">
        @include('examination::results.partials.school_header', ['header' => $header])
        <div class="doc-title">{{ _lang('Merit List') }}</div>
        <table class="exam-meta">
            <tr>
                <td><strong>{{ _lang('Exam') }}:</strong> {{ $sheet['exam_name'] ?? '' }}</td>
                <td><strong>{{ _lang('Class') }}:</strong> {{ $sheet['class_name'] ?? '' }}</td>
                <td><strong>{{ _lang('Section') }}:</strong> {{ $sheet['section_name'] ?? '' }}</td>
                <td><strong>{{ _lang('Session') }}:</strong> {{ $sheet['session_name'] ?? '' }}</td>
            </tr>
        </table>

        @if (($sheet['student_count'] ?? 0) === 0)
            <p><strong>{{ _lang('No students found for the selected criteria.') }}</strong></p>
        @else
            <table class="merit-table">
                <thead>
                    <tr>
                        <th>{{ _lang('Merit Position') }}</th>
                        <th class="name">{{ _lang('Student Name') }}</th>
                        <th>{{ _lang('Roll No') }}</th>
                        <th>{{ _lang('Class') }}</th>
                        <th>{{ _lang('Section') }}</th>
                        <th>{{ _lang('Total Marks') }}</th>
                        <th>{{ _lang('GPA') }}</th>
                        <th>{{ _lang('Grade') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sheet['rows'] as $row)
                        @php $session = $row['student_session'] ?? null; @endphp
                        <tr>
                            <td class="position">{{ $row['position'] ?? '-' }}</td>
                            <td class="name">{{ $row['student_name'] }}</td>
                            <td>{{ $row['roll'] }}</td>
                            <td>{{ $session?->class?->class_name ?? $sheet['class_name'] ?? '' }}</td>
                            <td>{{ $session?->section?->section_name ?? $sheet['section_name'] ?? '' }}</td>
                            <td>{{ is_numeric($row['total_marks'] ?? null) ? number_format((float) $row['total_marks'], 2) : '-' }}</td>
                            <td>{{ is_numeric($row['gpa'] ?? null) ? number_format((float) $row['gpa'], 2) : '-' }}</td>
                            <td>{{ $row['grade'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

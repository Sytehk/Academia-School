@extends('examination::results.print_layout')

@php
    $header = $sheet['header'] ?? [];
    $blankMarks = $sheet['blank_marks'] ?? false;
    $showPosition = $sheet['show_position'] ?? true;
    $theme = $header['theme'] ?? [];
    $primary = $theme['primary_color'] ?? '#1a365d';
@endphp

@section('extra_css')
    <style>
        body { font-size: 9px; padding: 8px; }
        .tabulation-table { width: 100%; border-collapse: collapse; table-layout: auto; }
        .tabulation-table th, .tabulation-table td {
            border: 1px solid #333;
            padding: 3px 4px;
            text-align: center;
            font-size: 8px;
            white-space: nowrap;
        }
        .tabulation-table th {
            background: {{ $primary }};
            color: #fff;
            font-weight: 700;
        }
        .tabulation-table tbody tr:nth-child(even) td { background: #f9fbfd; }
        .tabulation-table td.name, .tabulation-table th.name { text-align: left; min-width: 100px; white-space: normal; }
        .tabulation-table td.blank-cell { min-width: 42px; height: 20px; background: #fff !important; }
        .exam-meta { width: 100%; margin-bottom: 8px; font-size: 10px; border-collapse: collapse; }
        .exam-meta td { padding: 3px 6px; border: 1px solid #ddd; }
        .page-foot { margin-top: 10px; font-size: 9px; display: flex; justify-content: space-between; color: #555; }
        .sign-row td { height: 52px; text-align: center; vertical-align: bottom; font-size: 9px; border: 1px solid #333; }
        @page { size: landscape; margin: 8mm; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>
@endsection

@section('content')
    <div class="sheet-wrap">
        @include('examination::results.partials.school_header', ['header' => $header])
        <div class="doc-title">
            {{ $blankMarks ? _lang('Tabulation Sheet — Manual Mark Entry') : _lang('Tabulation Sheet / Broad Sheet') }}
        </div>
        <table class="exam-meta">
            <tr>
                <td><strong>{{ _lang('Exam') }}:</strong> {{ $sheet['exam_name'] ?? '' }}</td>
                <td><strong>{{ _lang('Class') }}:</strong> {{ $sheet['class_name'] ?? '' }}</td>
                <td><strong>{{ _lang('Section') }}:</strong> {{ $sheet['section_name'] ?? '' }}</td>
                <td><strong>{{ _lang('Group') }}:</strong> {{ $sheet['group_name'] ?? '-' }}</td>
                <td><strong>{{ _lang('Session') }}:</strong> {{ $sheet['session_name'] ?? '' }}</td>
            </tr>
        </table>

        @if (($sheet['student_count'] ?? 0) === 0)
            <p><strong>{{ _lang('No students found for the selected criteria.') }}</strong></p>
        @elseif (empty($sheet['subjects']))
            <p><strong>{{ _lang('No subjects configured for this class.') }}</strong></p>
        @else
            <table class="tabulation-table">
                <thead>
                    <tr>
                        <th>{{ _lang('SL') }}</th>
                        <th class="name">{{ _lang('Student Name') }}</th>
                        <th>{{ _lang('Roll') }}</th>
                        @foreach ($sheet['subjects'] as $subject)
                            @if (!empty($subject['components']) && count($subject['components']) > 1)
                                <th colspan="{{ count($subject['components']) + 1 }}">{{ $subject['name'] }} ({{ $subject['full_marks'] }})</th>
                            @else
                                <th>{{ $subject['name'] }} ({{ $subject['full_marks'] }})</th>
                            @endif
                        @endforeach
                        @unless ($blankMarks)
                            <th>{{ _lang('Total') }}</th>
                            <th>{{ _lang('%') }}</th>
                            <th>{{ _lang('GPA') }}</th>
                            <th>{{ _lang('Grade') }}</th>
                            <th>{{ _lang('Result') }}</th>
                            <th>{{ _lang('Failed') }}</th>
                            @if ($showPosition)
                                <th>{{ _lang('Position') }}</th>
                            @endif
                        @endunless
                    </tr>
                    @if (collect($sheet['subjects'])->contains(fn ($subject) => !empty($subject['components']) && count($subject['components']) > 1))
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            @foreach ($sheet['subjects'] as $subject)
                                @if (!empty($subject['components']) && count($subject['components']) > 1)
                                    @foreach ($subject['components'] as $component)
                                        <th>{{ $component['title'] }}</th>
                                    @endforeach
                                    <th>{{ _lang('Total') }}</th>
                                @else
                                    <th></th>
                                @endif
                            @endforeach
                            @unless ($blankMarks)
                                <th></th><th></th><th></th><th></th><th></th><th></th>
                                @if ($showPosition)
                                    <th></th>
                                @endif
                            @endunless
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @foreach ($sheet['rows'] as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="name">{{ $row['student_name'] }}</td>
                            <td>{{ $row['roll'] }}</td>
                            @foreach ($sheet['subjects'] as $subject)
                                @php $cell = $row['subject_cells'][$subject['id']] ?? ['obtained' => null, 'components' => []]; @endphp
                                @if (!empty($subject['components']) && count($subject['components']) > 1)
                                    @foreach ($subject['components'] as $componentIndex => $component)
                                        <td class="{{ $blankMarks ? 'blank-cell' : '' }}">
                                            @unless ($blankMarks)
                                                {{ $cell['components'][$componentIndex]['obtained'] ?? '' }}
                                            @endunless
                                        </td>
                                    @endforeach
                                    <td class="{{ $blankMarks ? 'blank-cell' : '' }}">
                                        @unless ($blankMarks)
                                            {{ $cell['obtained'] === null ? '' : number_format((float) $cell['obtained'], 1) }}
                                        @endunless
                                    </td>
                                @else
                                    <td class="{{ $blankMarks ? 'blank-cell' : '' }}">
                                        @unless ($blankMarks)
                                            {{ $cell['obtained'] === null ? '' : number_format((float) $cell['obtained'], 1) }}
                                        @endunless
                                    </td>
                                @endif
                            @endforeach
                            @unless ($blankMarks)
                                <td>{{ number_format((float) $row['total_marks'], 1) }}</td>
                                <td>{{ number_format((float) $row['percentage'], 2) }}</td>
                                <td>{{ number_format((float) $row['gpa'], 2) }}</td>
                                <td>{{ $row['grade'] }}</td>
                                <td>{{ strtoupper($row['status']) }}</td>
                                <td>{{ $row['failed_subject_count'] }}</td>
                                @if ($showPosition)
                                    <td>{{ $row['position'] ?? '-' }}</td>
                                @endif
                            @endunless
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if ($blankMarks)
            <table class="tabulation-table sign-row" style="margin-top:12px;">
                <tr>
                    <td>{{ _lang('Subject Teacher') }}</td>
                    <td>{{ _lang('Class Teacher') }}</td>
                    <td>{{ _lang('Exam Controller') }}</td>
                    <td>{{ _lang('Head Teacher') }}</td>
                    <td>{{ _lang('Date') }}: _______________</td>
                </tr>
            </table>
        @endif

        <div class="page-foot">
            <span>{{ _lang('Students') }}: {{ $sheet['student_count'] ?? 0 }} | {{ _lang('Subjects') }}: {{ count($sheet['subjects'] ?? []) }}</span>
            <span>{{ _lang('Generated') }}: {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>
@endsection

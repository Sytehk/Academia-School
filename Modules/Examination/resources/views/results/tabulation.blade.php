@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Tabulation Sheet / Broad Sheet') }}
@endsection
@section('content')
    <div class="card mb-5">
        <div class="card-header"><h3 class="card-title">{{ _lang('Generate Broad Sheet') }}</h3></div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Class') }}</label>
                    <select name="class_id" id="class_id_select" class="form-select" required>
                        <option value="">{{ _lang('Select Class') }}</option>
                        {!! create_option('classes', 'id', 'class_name', $filters['class_id'] ?? '') !!}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Section') }}</label>
                    <select name="section_id" id="section_id_select" class="form-select" data-selected="{{ $filters['section_id'] ?? '' }}">
                        <option value="">{{ _lang('Select Class First') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Exam / Term') }}</label>
                    <select name="exam_id" class="form-select" required>
                        <option value="">{{ _lang('Select Exam') }}</option>
                        @foreach ($exams as $examItem)
                            <option value="{{ $examItem->id }}" @selected(($filters['exam_id'] ?? '') == $examItem->id)>{{ $examItem->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Sheet Type') }}</label>
                    <select name="with_results" class="form-select">
                        <option value="0" @selected(($filters['with_results'] ?? '0') === '0')>{{ _lang('Blank marks (manual entry)') }}</option>
                        <option value="1" @selected(($filters['with_results'] ?? '') === '1')>{{ _lang('With calculated results') }}</option>
                        <option value="2" @selected(($filters['with_results'] ?? '') === '2')>{{ _lang('With calculated results (Without Position)') }}</option>
                    </select>
                </div>
                <div class="col-md-12 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">{{ _lang('Generate') }}</button>
                </div>
            </form>
        </div>
    </div>

    @if ($sheet)
        <div class="d-flex gap-2 mb-3 align-items-center">
            <a class="btn btn-success" target="_blank" href="{{ route('tabulation-sheet-print', request()->query()) }}">{{ _lang('Print') }}</a>
            <a class="btn btn-light" href="{{ route('tabulation-sheet-pdf', request()->query()) }}">{{ _lang('PDF') }}</a>
            <span class="text-muted ms-2">
                {{ ($sheet['blank_marks'] ?? false) ? _lang('Manual entry sheet — mark cells are blank.') : _lang('Result sheet with calculated totals.') }}
                {{ _lang('Students') }}: {{ $sheet['student_count'] ?? 0 }},
                {{ _lang('Subjects') }}: {{ count($sheet['subjects'] ?? []) }}
            </span>
        </div>
        <div class="card">
            <div class="card-body table-responsive" style="max-height: 70vh;">
                @if (($sheet['student_count'] ?? 0) === 0)
                    <div class="alert alert-warning">{{ _lang('No students found for the selected class/section in the current academic session.') }}</div>
                @elseif (empty($sheet['subjects']))
                    <div class="alert alert-warning">{{ _lang('No subjects configured for this class/group.') }}</div>
                @else
                    <table class="table table-bordered table-sm table-striped" style="min-width: 1100px;">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th>{{ _lang('SL') }}</th>
                                <th>{{ _lang('Student') }}</th>
                                <th>{{ _lang('Roll') }}</th>
                                @foreach ($sheet['subjects'] as $subject)
                                    <th>{{ $subject['name'] }} ({{ $subject['full_marks'] }})</th>
                                @endforeach
                                @unless ($sheet['blank_marks'] ?? false)
                                    <th>{{ _lang('Total') }}</th>
                                    <th>{{ _lang('GPA') }}</th>
                                    <th>{{ _lang('Grade') }}</th>
                                    @if ($sheet['show_position'] ?? true)
                                        <th>{{ _lang('Position') }}</th>
                                    @endif
                                @endunless
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sheet['rows'] as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row['student_name'] }}</td>
                                    <td>{{ $row['roll'] }}</td>
                                    @foreach ($sheet['subjects'] as $subject)
                                        <td style="min-width:48px;">
                                            @if ($sheet['blank_marks'] ?? false)
                                                &nbsp;
                                            @else
                                                {{ $row['subject_cells'][$subject['id']]['obtained'] ?? '-' }}
                                            @endif
                                        </td>
                                    @endforeach
                                    @unless ($sheet['blank_marks'] ?? false)
                                        <td>{{ number_format((float) $row['total_marks'], 2) }}</td>
                                        <td>{{ number_format((float) $row['gpa'], 2) }}</td>
                                        <td>{{ $row['grade'] }}</td>
                                        @if ($sheet['show_position'] ?? true)
                                            <td>{{ $row['position'] ?? '-' }}</td>
                                        @endif
                                    @endunless
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif
@endsection


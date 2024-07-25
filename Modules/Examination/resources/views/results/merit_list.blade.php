@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Merit List Sheet') }}
@endsection
@section('content')
    <div class="card mb-5">
        <div class="card-header"><h3 class="card-title">{{ _lang('Generate Merit List') }}</h3></div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ _lang('Class') }}</label>
                    <select name="class_id" id="class_id_select" class="form-select" required>
                        <option value="">{{ _lang('Select Class') }}</option>
                        {!! create_option('classes', 'id', 'class_name', $filters['class_id'] ?? '') !!}
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ _lang('Section') }}</label>
                    <select name="section_id" id="section_id_select" class="form-select" data-selected="{{ $filters['section_id'] ?? '' }}">
                        <option value="">{{ _lang('All Sections') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ _lang('Exam / Term') }}</label>
                    <select name="exam_id" class="form-select" required>
                        <option value="">{{ _lang('Select Exam') }}</option>
                        @foreach ($exams as $examItem)
                            <option value="{{ $examItem->id }}" @selected(($filters['exam_id'] ?? '') == $examItem->id)>{{ $examItem->name }}</option>
                        @endforeach
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
            <a class="btn btn-success" target="_blank" href="{{ route('merit-list-sheet-print', request()->query()) }}">{{ _lang('Print') }}</a>
            <a class="btn btn-light" href="{{ route('merit-list-sheet-pdf', request()->query()) }}">{{ _lang('PDF') }}</a>
            <a class="btn btn-light" href="{{ route('merit-list-sheet-excel', request()->query()) }}">{{ _lang('Excel') }}</a>
            <span class="text-muted ms-2">
                {{ _lang('Students') }}: {{ $sheet['student_count'] ?? 0 }}
            </span>
        </div>
        <div class="card">
            <div class="card-body table-responsive" style="max-height: 70vh;">
                @if (($sheet['student_count'] ?? 0) === 0)
                    <div class="alert alert-warning">{{ _lang('No students found for the selected class/section in the current academic session.') }}</div>
                @else
                    <table class="table table-bordered table-sm table-striped">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th>{{ _lang('Merit Position') }}</th>
                                <th>{{ _lang('Student Name') }}</th>
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
                                    <td>{{ $row['position'] ?? '-' }}</td>
                                    <td>{{ $row['student_name'] }}</td>
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
        </div>
    @endif
@endsection

@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Empty Manual Mark Sheet') }}
@endsection
@section('content')
    <div class="card mb-5">
        <div class="card-header"><h3 class="card-title">{{ _lang('Print Empty Mark Input Sheet') }}</h3></div>
        <div class="card-body">
            <form method="GET" action="{{ route('empty-mark-sheet-print') }}" target="_blank" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Class') }}</label>
                    <select name="class_id" id="class_id_select" class="form-select" required>
                        <option value="">{{ _lang('Select Class') }}</option>
                        {!! create_option('classes', 'id', 'class_name', $filters['class_id'] ?? '') !!}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Section') }}</label>
                    <select name="section_id" id="section_id_select" class="form-select"
                        data-selected="{{ $filters['section_id'] ?? '' }}">
                        <option value="">{{ _lang('Select Class First') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Exam') }}</label>
                    <select name="exam_id" class="form-select" required>
                        <option value="">{{ _lang('Select Exam') }}</option>
                        @foreach ($exams as $examItem)
                            <option value="{{ $examItem->id }}">{{ $examItem->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Number of students (optional)') }}</label>
                    <input type="number" min="1" name="student_limit" class="form-control">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary">{{ _lang('Print Empty Sheet') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

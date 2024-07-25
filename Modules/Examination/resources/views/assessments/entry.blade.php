@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Domain Assessment Entry') }}
@endsection
@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Class') }}</label>
                    <select name="class_id" class="form-select" required>
                        {!! create_option('classes', 'id', 'class_name', $filters['class_id'] ?? '') !!}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Exam') }}</label>
                    <select name="exam_id" class="form-select" required>
                        <option value="">{{ _lang('Select Exam') }}</option>
                        @foreach ($exams as $examItem)
                            <option value="{{ $examItem->id }}" @selected(($filters['exam_id'] ?? '') == $examItem->id)>{{ $examItem->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary">{{ _lang('Load Students') }}</button>
                </div>
            </form>
        </div>
    </div>

    @if ($students->isNotEmpty())
        <form method="POST" action="{{ route('assessment-entry.store') }}">
            @csrf
            <input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
            <input type="hidden" name="exam_id" value="{{ $filters['exam_id'] }}">
            @foreach ($students as $session)
                @php $saved = $existing->get($session->student_id, collect())->keyBy('assessment_item_id'); @endphp
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">{{ $session->student?->first_name }} {{ $session->student?->last_name }} ({{ _lang('Roll') }} {{ $session->roll }})</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Domain') }}</th>
                                    <th>{{ _lang('Item') }}</th>
                                    <th>{{ _lang('Rating') }}</th>
                                    <th>{{ _lang('Score') }}</th>
                                    <th>{{ _lang('Remarks') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($domains as $domain)
                                    @foreach ($domain->items as $item)
                                        @php $row = $saved->get($item->id); @endphp
                                        <tr>
                                            <td>{{ $domain->name }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>
                                                @if ($item->rating_type === 'scale' && $item->grade_scale)
                                                    <select class="form-select form-select-sm" name="entries[{{ $session->student_id }}][{{ $item->id }}][rating]">
                                                        <option value="">-</option>
                                                        @foreach ($item->grade_scale as $scale)
                                                            <option value="{{ $scale }}" @selected(($row->rating ?? '') == $scale)>{{ $scale }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <input class="form-control form-control-sm" name="entries[{{ $session->student_id }}][{{ $item->id }}][rating]" value="{{ $row->rating ?? '' }}">
                                                @endif
                                            </td>
                                            <td><input type="number" step="0.01" class="form-control form-control-sm" name="entries[{{ $session->student_id }}][{{ $item->id }}][score]" value="{{ $row->score ?? '' }}"></td>
                                            <td><input class="form-control form-control-sm" name="entries[{{ $session->student_id }}][{{ $item->id }}][remarks]" value="{{ $row->remarks ?? '' }}"></td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
            <button class="btn btn-primary mb-5">{{ _lang('Save Assessments') }}</button>
        </form>
    @endif
@endsection

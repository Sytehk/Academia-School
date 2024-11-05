@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Exam Results') }}
@endsection

@section('content')
    @include('parentmodule::partials.child_context')

    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">{{ _lang('Select Exam') }}</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('parent.exam-results') }}" class="d-flex flex-wrap gap-3">
                <select name="exam_id" class="form-select form-select-solid w-auto flex-grow-1">
                    <option value="">{{ _lang('-- Select an exam --') }}</option>
                    @foreach ($exams as $classExam)
                        @php
                            $exam = $classExam->exam;
                        @endphp
                        @if ($exam)
                            <option value="{{ $exam->id }}" {{ $selectedExamId == $exam->id ? 'selected' : '' }}>
                                {{ $exam->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">{{ _lang('View Result') }}</button>
            </form>
        </div>
    </div>

    @if ($result)
        @if (!empty($result['found']))
            @php
                $exam = $result['exam'];
            @endphp
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $exam['name'] }} — {{ _lang('Mark Sheet') }}</h3>
                    <div class="card-toolbar">
                        <span class="badge badge-lg badge-{{ $exam['result'] === _lang('Pass') ? 'success' : 'danger' }}">
                            {{ $exam['result'] }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-6">
                        @php
                            $tiles = [
                                _lang('Obtained') => $exam['obtained_marks'] . ' / ' . $exam['total_marks'],
                                _lang('Percentage') => $exam['percentage'] . '%',
                                _lang('Grade') => $exam['grade'],
                                _lang('GPA') => $exam['grade_point'],
                                _lang('Position') => $result['position'],
                            ];
                        @endphp
                        @foreach ($tiles as $label => $value)
                            <div class="col-6 col-md-4 col-xl">
                                <div class="border border-dashed rounded p-3 text-center">
                                    <div class="fs-3 fw-bold text-gray-900">{{ $value }}</div>
                                    <div class="fs-8 text-muted">{{ $label }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-dashed gy-2 align-middle">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4">{{ _lang('Subject') }}</th>
                                    <th class="text-center">{{ _lang('Full Marks') }}</th>
                                    <th class="text-center">{{ _lang('Obtained') }}</th>
                                    <th class="text-center">{{ _lang('Grade') }}</th>
                                    <th class="pe-4">{{ _lang('Remarks') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($result['subjects'] as $subject)
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $subject['name'] }}</td>
                                        <td class="text-center">{{ $subject['full_marks'] }}</td>
                                        <td class="text-center">{{ $subject['marks_obtained'] }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-light-primary">{{ $subject['grade'] }}</span>
                                        </td>
                                        <td class="pe-4">{{ $subject['remarks'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-muted text-center py-10">{{ _lang('No result found for the selected exam.') }}</div>
            </div>
        @endif
    @endif
@endsection

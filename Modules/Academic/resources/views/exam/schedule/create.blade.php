@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Exam Schedule') }}
@endsection

<style>
    #exam_routine {
        font-size: 12px !important;
    }
</style>

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">{{ _lang('Exam Schedule') }}</div>

                <div class="">
                    <form method="post" class="params-panel needs-validation" autocomplete="off"
                        action="{{ $type == 'create' ? url('exams-schedule/create') : url('exams-schedule') }}" novalidate>
                        @csrf

                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">{{ _lang('Select Exam') }}</label>
                                <select class="form-select" id="dropdownData" name="exam" required>
                                    {!! create_option('exams', 'id', 'name', old('exam_id')) !!}
                                </select>
                                <div class="invalid-feedback">Please select an exam.</div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">{{ _lang('Exam Start At') }}</label>
                                <input type="datetime-local" class="form-control" name="date_time" value="" required
                                    id="setDateTime">
                                <div class="invalid-feedback">Please provide a valid date and time.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">{{ _lang('Select Class') }}</label>
                                <select class="form-select" name="class" required>
                                    {!! create_option('classes', 'id', 'class_name', isset($class) ? $class : old('class')) !!}
                                </select>
                                <div class="invalid-feedback">Please select a class.</div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <label class="form-label">{{ _lang('Select Group') }}</label>
                                <select class="form-select" name="group">
                                    {!! create_option('student_groups', 'id', 'group_name', isset($group) ? $class : old('group')) !!}
                                </select>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">{{ _lang('Search') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (isset($subjects) && $type == 'create')
                @if (count($subjects) > 0)
                    <div class="card mt-3">
                        <div class="card-header">{{ _lang('Exam Schedule') }}</div>
                        <div class="">
                            <form action="{{ url('exams/store_schedule') }}" method="post">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ _lang('Subject') }}</th>
                                                <th>{{ _lang('Date') }}</th>
                                                <th>{{ _lang('Start Time') }}</th>
                                                <th>{{ _lang('End Time') }}</th>
                                                <th>{{ _lang('Room') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($subjects as $subject)
                                                @php $subjectId = 'subject_ids[' . $subject->subject_id . ']'; @endphp
                                                <tr>
                                                    <input type="hidden" name="schedules_id[]"
                                                        value="{{ $subject->schedules_id }}">
                                                    <input type="hidden" name="{{ $subjectId }}"
                                                        value="{{ $subject->subject_id }}">
                                                    <input type="hidden" name="class_id" value="{{ $class }}">
                                                    <input type="hidden" name="group_id" value="{{ $group }}">
                                                    <input type="hidden" name="exam_id" value="{{ $exam }}">
                                                    <input type="hidden" name="exam_type_id" value="{{ $examTypeName }}">
                                                    <input type="hidden" name="date_time" value="{{ $examDateTime }}">

                                                    <td>{{ $subject->subject_name }}</td>
                                                    @foreach (['date', 'start_time', 'end_time', 'room'] as $name)
                                                        <td>
                                                            <input type="text"
                                                                class="form-control {{ $name === 'date' ? 'datepicker' : ($name === 'start_time' || $name === 'end_time' ? 'timepicker' : '') }}"
                                                                value="{{ $subject->$name ?? '' }}"
                                                                name="{{ $subjectId }}[{{ $name }}]"
                                                                required />
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td colspan="5">
                                                    <button type="submit"
                                                        class="btn btn-primary float-end">{{ _lang('Save') }}</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-danger mt-3">
                        <h5>{{ _lang('No Subject Assign for this class!') }}</h5>
                    </div>
                @endif
            @endif

            @if (isset($subjects) && $type == 'view')
                @if (count($subjects) > 0)
                    <div class="card mt-3">
                        <div class="card-header d-flex justify-content-between">
                            <span>{{ _lang('Exam Routine') }}</span>
                            <button type="button" data-print="exam_routine" class="btn btn-primary btn-sm">
                                <i class="fa fa-print"></i> {{ _lang('Print Routine') }}
                            </button>
                        </div>
                        <div class="" id="exam_routine">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>{{ _lang('Subject') }}</th>
                                            <th>{{ _lang('Date') }}</th>
                                            <th>{{ _lang('Start Time') }}</th>
                                            <th>{{ _lang('End Time') }}</th>
                                            <th>{{ _lang('Room') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subjects as $subject)
                                            <tr>
                                                <td>{{ $subject->subject_name }}</td>
                                                <td>{{ $subject->date }}</td>
                                                <td>{{ $subject->start_time }}</td>
                                                <td>{{ $subject->end_time }}</td>
                                                <td>{{ $subject->room }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-danger mt-3">
                        <h5>{{ _lang('No Subject Assign for this class!') }}</h5>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        document.querySelector('#dropdownData').addEventListener('change', function() {
            var selectedValue = this.value;
            $.ajax({
                type: 'GET',
                url: '/exams/get-data-by-selection',
                data: {
                    selected_value: selectedValue
                },
                success: function(response) {
                    $('#setDateTime').val(response.data.date_time);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        function updateOutput() {
            const input1 = parseFloat(document.getElementById('objID').value || 0);
            const input2 = parseFloat(document.getElementById('writtenID').value || 0);
            const input3 = parseFloat(document.getElementById('practicalID').value || 0);
            document.getElementById('totalID').value = input1 + input2 + input3;
        }

        window.onload = updateOutput;
    </script>
@endsection

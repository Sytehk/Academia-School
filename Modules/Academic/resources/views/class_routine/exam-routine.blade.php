@extends('layouts.backend')

<style>
    #exam_routine {
        font-size: 12px !important;
    }
</style>

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Card for Exam Schedule Form -->
            <div class="card">
                <div class="card-header">
                    <h5>{{ _lang('Exam Schedule') }}</h5>
                </div>

                <div class="card-body">
                    <form method="post" class="params-panel validate" autocomplete="off"
                        action="{{ url('exam-routines/create') }}">
                        {{ csrf_field() }}

                        <div class="row">
                            <!-- Select Exam -->
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="form-label">{{ _lang('Select Exam') }}</label>
                                    <select class="form-control select2" id="dropdownData" name="exam">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        {!! create_option('exams', 'id', 'name', old('exam_id')) !!}
                                    </select>
                                </div>
                            </div>

                            <!-- Select Class -->
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="form-label">{{ _lang('Select Class') }}</label>
                                    <select class="form-control select2" name="class">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        {!! create_option('classes', 'id', 'class_name', old('class')) !!}
                                    </select>
                                </div>
                            </div>

                            <!-- Select Group -->
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="form-label">{{ _lang('Select Group') }}</label>
                                    <select class="form-control select2" name="group">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        {!! create_option('student_groups', 'id', 'group_name', old('group')) !!}
                                    </select>
                                </div>
                            </div>

                            <!-- Search Button -->
                            <div class="col-md-6 mb-3">
                                <button type="submit" class="btn btn-primary w-100 mt-4">{{ _lang('Search') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (isset($subjects) && count($subjects) > 0)
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ url('exam-routines/view') }}?exam={{ $exam }}&class={{ $class }}&group={{ $group }}"
                        class="btn btn-outline-secondary btn-sm {{ $type == 'view' ? 'active' : '' }}">
                        <i class="fa fa-eye"></i> {{ _lang('View Routine') }}
                    </a>
                    <a href="{{ url('exam-routines/create') }}?exam={{ $exam }}&class={{ $class }}&group={{ $group }}"
                        class="btn btn-outline-primary btn-sm {{ $type == 'create' ? 'active' : '' }}">
                        <i class="fa fa-pencil"></i> {{ _lang('Edit Routine') }}
                    </a>
                </div>
            @endif

            <!-- View Exam Schedule (read-only) -->
            @if (isset($subjects) && $type != 'create')
                @if (count($subjects) > 0)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5>{{ _lang('Exam Routine') }}</h5>
                        </div>

                        <div class="card-body">
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
                                        @if ($subject->date)
                                            <tr>
                                                <td>{{ $subject->subject_name }}</td>
                                                <td>{{ $subject->date }}</td>
                                                <td>{{ $subject->start_time ? substr($subject->start_time, 0, 5) : '' }}</td>
                                                <td>{{ $subject->end_time ? substr($subject->end_time, 0, 5) : '' }}</td>
                                                <td>{{ $subject->room }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>

                            @if ($subjects->every(fn($s) => ! $s->date))
                                <p class="text-muted mb-0">{{ _lang('No exam routine has been scheduled for this class/exam yet.') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            @endif

            <!-- Create Exam Schedule -->
            @if (isset($subjects) && $type == 'create')
                @if (count($subjects) > 0)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5>{{ _lang('Exam Schedule') }}</h5>
                        </div>

                        <div class="card-body">
                            <form action="{{ url('exam-routines-store') }}" method="post">
                                {{ csrf_field() }}
                                <table class="table table-bordered" id="examRoutineTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" id="selectAllSubjects" aria-label="Select all">
                                            </th>
                                            <th>{{ _lang('Subject') }}</th>
                                            <th>{{ _lang('Date') }}</th>
                                            <th>{{ _lang('Start Time') }}</th>
                                            <th>{{ _lang('End Time') }}</th>
                                            <th>{{ _lang('Room') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subjects as $subject)
                                            @php $isScheduled = ! empty($subject->date); @endphp
                                            <tr>
                                                <input type="hidden" name="subject_ids[{{ $subject->subject_id }}]"
                                                    value="{{ $subject->subject_id }}">
                                                <input type="hidden" name="class_id" value="{{ $class }}">
                                                <input type="hidden" name="group_id" value="{{ $group }}">
                                                <input type="hidden" name="exam_id" value="{{ $exam }}">

                                                <td class="text-center">
                                                    <input type="checkbox" class="subject-select"
                                                        name="subject_ids[{{ $subject->subject_id }}][selected]"
                                                        value="1" {{ $isScheduled ? 'checked' : '' }}
                                                        aria-label="{{ _lang('Include') }} {{ $subject->subject_name }}">
                                                </td>
                                                <td>{{ $subject->subject_name }}</td>
                                                <td>
                                                    <input type="date" class="form-control subject-field"
                                                        name="subject_ids[{{ $subject->subject_id }}][date]"
                                                        value="{{ $subject->date }}">
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control subject-field"
                                                        name="subject_ids[{{ $subject->subject_id }}][start_time]"
                                                        value="{{ $subject->start_time ? substr($subject->start_time, 0, 5) : '' }}">
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control subject-field"
                                                        name="subject_ids[{{ $subject->subject_id }}][end_time]"
                                                        value="{{ $subject->end_time ? substr($subject->end_time, 0, 5) : '' }}">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control subject-field"
                                                        name="subject_ids[{{ $subject->subject_id }}][room]"
                                                        value="{{ $subject->room }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="6">
                                                <button type="submit"
                                                    class="btn btn-primary w-100">{{ _lang('Save') }}</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-danger mt-3">
                        <h5>{{ _lang('No Subject Assigned for this Class!') }}</h5>
                    </div>
                @endif
            @endif

        </div>
    </div>
@endsection

@section('js-script')
    <script>
        $('#dropdownData').on('change', function() {
            var selectedValue = $(this).val();

            $.ajax({
                type: 'GET',
                url: '/exams/get-data-by-selection',
                data: {
                    selected_value: selectedValue
                },
                success: function(response) {
                    $('#setDateTime').val(response.data.date_time);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        });

        // Only ticked subjects get scheduled — their Date/Start/End/Room fields stay
        // enabled and required; unticked subjects' fields are disabled so they can't be
        // filled by mistake and aren't submitted with the form (FEAT-004).
        function syncSubjectRow(checkbox) {
            var row = checkbox.closest('tr');
            var fields = row.querySelectorAll('.subject-field');
            var checked = checkbox.checked;

            fields.forEach(function(field) {
                field.disabled = !checked;
                if (field.type === 'date' || field.type === 'time') {
                    field.required = checked;
                }
                if (!checked) {
                    field.value = '';
                }
            });
        }

        document.querySelectorAll('#examRoutineTable .subject-select').forEach(function(checkbox) {
            syncSubjectRow(checkbox);
            checkbox.addEventListener('change', function() {
                syncSubjectRow(checkbox);
            });
        });

        var selectAll = document.getElementById('selectAllSubjects');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('#examRoutineTable .subject-select').forEach(function(checkbox) {
                    checkbox.checked = selectAll.checked;
                    syncSubjectRow(checkbox);
                });
            });
        }
    </script>
@endsection

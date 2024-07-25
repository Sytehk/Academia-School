@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Mark Input (Section Wise)') }}
@endsection
@section('content')
    <div class="row mb-5">
        <div class="col-lg-4">
            <div class="card-body table-responsive border p-5">
                <div class="card-header">
                    <h2>Mark Input Form</h2>
                </div>
                <form method="get" action="{{ route('mark-input-section-wise-class', ['class_id' => $classId]) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="control-label my-2">Exam <span class="text-danger">*</span></label>
                        <select name="exam_id" class="form-select form-select-sm form-select-solid" data-control="select2"
                            required>
                            <option value="" disabled>-- Select Exam --</option>
                            @foreach ($classExams as $classExam)
                                <option {{ $examId == $classExam->exam?->id ? 'selected' : '' }}
                                    value="{{ $classExam->exam?->id }}">{{ $classExam->exam?->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="control-label my-2">Group <span class="text-danger">*</span></label>
                        <select name="group_id" id="mark_input_group_id" class="form-select form-select-sm form-select-solid" data-control="select2">
                            {!! create_option('student_groups', 'id', 'group_name', $groupId) !!}
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="control-label my-2">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" id="mark_input_subject_id" class="form-select form-select-sm form-select-solid"
                            data-control="select2">
                            <option value="" disabled>-- Select Subject --</option>
                            @foreach ($subjects as $subject)
                                <option {{ $subjectId == $subject->id ? 'selected' : '' }} value="{{ $subject->id }}">
                                    {{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> Search
                        </button>
                    </div>
                </form>

                @if ($examId && $subjectId)
                    @php
                        $sheetParams = array_filter([
                            'exam_id' => $examId,
                            'class_id' => $classId,
                            'subject_id' => $subjectId,
                            'group_id' => $groupId,
                        ]);
                    @endphp
                    <div class="border-top mt-5 pt-5">
                        <h5>{{ _lang('Offline Mark Sheet') }}</h5>
                        <a href="{{ route('mark-sheet-export', $sheetParams + ['format' => 'xlsx']) }}"
                            class="btn btn-sm btn-light-primary mb-3">
                            <i class="fa fa-download"></i> {{ _lang('Download Sheet') }}
                        </a>
                        <form method="post" action="{{ route('mark-sheet-import') }}" enctype="multipart/form-data">
                            @csrf
                            @foreach ($sheetParams as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <input type="file" name="file" accept=".csv,.xlsx" class="form-control form-control-sm mb-2" required>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fa fa-upload"></i> {{ _lang('Import Marks') }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        @if ($examId && $groupId && $subjectId && $markConfig)
            <div class="col-lg-8">
                <div class="row g-0">
                    <div class="col-12 p-0 pt-0">
                        <div class="table-responsive bg-cyan border border-primary m-2 rounded">
                            <div class="p-3 border-bottom">
                                <label>
                                    @php
                                        $classData = Modules\Academic\Models\ClassModel::where('id', $classId)->first();
                                    @endphp
                                    <h4>Class - {{ $classData->class_name }}</h4>
                                    <span>(Marking Scale)</span>
                                </label>
                            </div>
                            <div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="thead-light">
                                            <tr class="text-center">
                                                <th><b>Exam Code Title</b></th>
                                                <th><b>Total</b></th>
                                                <th><b>Pass Mark</b></th>
                                                <th><b>Acceptance</b></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($markConfig as $single)
                                                <tr class="text-center">
                                                    <td>{{ $single->mark_config_exam_code?->title }}</td>
                                                    <td>{{ $single->mark_config_exam_code?->total_marks }}</td>
                                                    <td>{{ $single->mark_config_exam_code?->pass_mark }}</td>
                                                    <td>{{ $single->mark_config_exam_code?->acceptance }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

    @if ($examId && $groupId && $subjectId && $markConfig)
        <div class="row mt-5">
            <div class="col-lg-12">
                <form action="{{ route('mark-store-section-wise') }}" method="POST">
                    @csrf

                    <!-- Hidden Inputs for IDs -->
                    <input type="hidden" name="class_id" value="{{ $classId }}">
                    <input type="hidden" name="group_id" value="{{ $groupId }}">
                    <input type="hidden" name="subject_id" value="{{ $subjectId }}">
                    <input type="hidden" name="exam_id" value="{{ $examId }}">


                    <div class="table-responsive">
                        <div class="bg-primary text-white p-2">
                            <span>Student List</span>
                        </div>
                        <div>
                            <table class="table table-striped table-bordered">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>
                                            <b>Student ID</b>
                                        </th>
                                        <th><b>Roll No.</b></th>
                                        <th><b>Name</b></th>

                                        @foreach ($markConfig as $single1)
                                            <th><b>{{ $single1->mark_config_exam_code?->title }}</b></th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $student)
                                        <tr class="text-center">
                                            <td>{{ $student->id }}</td>
                                            <td>{{ $student->roll }}</td>
                                            <td>{{ $student->name }}</td>

                                            @foreach ($markConfig as $single2)
                                                <td>
                                                    <input type="number" min="0"
                                                        max="{{ $single2->mark_config_exam_code?->total_marks }}"
                                                        name="marks[{{ $student->id }}][{{ $single2->mark_config_exam_code?->id }}]"
                                                        class="form-control"
                                                        placeholder="max: {{ $single2->mark_config_exam_code?->total_marks }}"
                                                        value="">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row align-items-center mt-2">
                        <div class="col text-start">
                            <span>Total Found: {{ count($students) }} </span>
                        </div>
                        <div class="col text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@section('js-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var groupSelect = document.getElementById('mark_input_group_id');
            var subjectSelect = document.getElementById('mark_input_subject_id');

            if (!groupSelect || !subjectSelect) {
                return;
            }

            groupSelect.addEventListener('change', function() {
                var classId = {{ (int) $classId }};
                var groupId = this.value;
                var currentSubjectId = subjectSelect.value;

                fetch("{{ url('mark-input-subjects') }}/" + classId + "?group_id=" + encodeURIComponent(groupId))
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(subjects) {
                        subjectSelect.innerHTML = '<option value="" disabled>-- Select Subject --</option>';

                        subjects.forEach(function(subject) {
                            var option = document.createElement('option');
                            option.value = subject.id;
                            option.textContent = subject.subject_name;
                            if (String(subject.id) === String(currentSubjectId)) {
                                option.selected = true;
                            }
                            subjectSelect.appendChild(option);
                        });

                        if (window.jQuery) {
                            jQuery(subjectSelect).trigger('change');
                        }
                    });
            });
        });
    </script>
@endsection

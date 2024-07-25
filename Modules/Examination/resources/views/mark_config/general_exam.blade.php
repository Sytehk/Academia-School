<div class="my-2 row">
    <div class="col-lg-12">
        <div class="border card-body">
            <form method="get">
                @csrf
                <div class="row justify-content-center">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="mb-2 control-label" for="class_id_select">{{ _lang('Class') }}</label>
                            <select name="class_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" required>
                                {!! create_option('classes', 'id', 'class_name', request()->class_id) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="mb-2 control-label">{{ _lang('Group') }}</label>
                            <select name="group_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2">
                                {!! create_option('student_groups', 'id', 'group_name', request()->group_id) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="form-group">
                            <button type="submit"
                                class="btn btn-primary btn-block margin_top_24">{{ _lang('Search') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if (isset($subjects) &&
        count($subjects) > 0 &&
        isset($examCodes) &&
        count($examCodes) > 0 &&
        isset($classExams) &&
        count($classExams) > 0)

    <div class="my-2 row">
        <div class="col-lg-12">
            <div class="border card-body">
                <form action="{{ route('general-exam-store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $classId }}">
                    <input type="hidden" name="group_id" value="{{ $groupId }}">

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="p-5 mb-3 border">
                                <label class="my-2 control-label">
                                    Select Subject <span class="text-danger">*</span>
                                </label>
                                <div class="table-responsive ">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="table_td_2">
                                                    <input type="checkbox" id="select-all-subjects"
                                                        aria-label="Select all">
                                                </th>
                                                <th scope="col"><b>Subject</b></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($subjects as $subject)
                                                <tr>
                                                    <td class="table_td_2">
                                                        <input type="checkbox" name="selected_subjects[]"
                                                            value="{{ $subject->id }}" id="subject-{{ $subject->id }}"
                                                            aria-label="Select"
                                                            {{ in_array($subject->id, old('selected_subjects', [])) ? 'checked' : '' }}>
                                                    </td>
                                                    <td>{{ $subject->subject_name }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="p-5 mb-3 border">
                                <label class="my-2 control-label">
                                    Remove Exam Code from list (If required) <span class="text-danger">*</span>
                                </label>
                                <div class="table-responsive ">
                                    <table class="table" id="exam-codes-table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Exam Code Title</th>
                                                <th scope="col">Total Marks</th>
                                                <th scope="col">Pass Mark</th>
                                                <th scope="col">Acceptance</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($examCodes as $examCode)
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="exam_codes[{{ $loop->index }}][title]"
                                                            value="{{ $examCode->short_code_title }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="exam_codes[{{ $loop->index }}][total_marks]"
                                                            value="{{ $examCode->total_mark }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="exam_codes[{{ $loop->index }}][pass_mark]"
                                                            value="{{ $examCode->pass_mark }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            name="exam_codes[{{ $loop->index }}][acceptance]"
                                                            value="{{ $examCode->accept_percent }}">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm remove-row">
                                                            <svg stroke="currentColor" fill="currentColor"
                                                                viewBox="0 0 24 24" height="1em" width="1em">
                                                                <path
                                                                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="my-5 text-center">
                                    <span class="text-danger ">
                                        <strong>N.B.: You can input or change the value of the field as per your
                                            requirement.</strong>
                                    </span>
                                </div>

                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="p-5 mb-3 border">
                                <label class="my-2 control-label">
                                    Select Exam <span class="text-danger">*</span>
                                </label>
                                <div class="table-responsive ">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="table_td_2">
                                                    <input type="checkbox" id="select-all-exams"
                                                        aria-label="Select all">
                                                </th>
                                                <th scope="col"><b>Exam <span class="text-danger">*</span></b></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($classExams as $classExam)
                                                <tr>
                                                    <td class="table_td_2">
                                                        <input type="checkbox" name="selected_exams[]"
                                                            value="{{ $classExam->id }}"
                                                            id="exam-{{ $classExam->id }}" aria-label="Select"
                                                            {{ in_array($classExam->id, old('selected_exams', [])) ? 'checked' : '' }}>
                                                    </td>
                                                    <td>{{ $classExam->exam?->name }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-5">
                                <div class="d-block">
                                    <button type="submit" name="save" class="btn btn-primary">
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@else
    <div class="mt-5 row justify-content-center">
        <div class="col-lg-12">
            <p class="mt-5 text-center text-danger">No subjects, exam codes, or class exams available.</p>
        </div>
    </div>
@endif



@section('scripts')
    <script>
        // Select all subjects
        document.getElementById('select-all-subjects').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_subjects[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Select all exams
        document.getElementById('select-all-exams').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_exams[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Remove row functionality
        document.querySelectorAll('.remove-row').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                row.remove();
            });
        });
    </script>
@endsection

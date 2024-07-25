<div class="my-2 row">
    <div class="col-lg-4">
        <div class="p-5 border table-responsive">
            <form method="post" action="{{ route('exam-grade-store') }}" onsubmit="return validateForm2()">
                @csrf

                <div class="mb-3">
                    <label class="my-2 control-label">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id_select2" class="form-select form-select-sm form-select-solid"
                        data-control="select2" required>

                        {!! create_option('classes', 'id', 'class_name', request()->class_id) !!}
                    </select>
                </div>

                <div class="mb-3">
                    <label class="my-2 control-label">
                        <strong>Select Global Exam Grade List</strong>
                        <span class="text-danger">*</span>
                    </label>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        <input type="checkbox" class="form-check-input" id="select-all2"
                                            aria-label="Select all">
                                    </th>
                                    <th scope="col">Grade</th>
                                    <th scope="col">Grade Range</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($grades as $grade)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input" name="selected_grades[]"
                                                value="{{ $grade->id }}" aria-label="Select SC-{{ $grade->id }}">
                                        </td>
                                        <td>{{ $grade->grade_name }}</td>
                                        <td>{{ $grade->grade_range }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="p-5 border card-body table-responsive">
            <form action="{{ route('exam-grade-update') }}" method="POST">
                @csrf

                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase gs-0">
                            <th>
                                <input type="checkbox" class="form-check-input" id="select-all1222"
                                    aria-label="Select all">
                            </th>
                            <th>{{ _lang('Class') }}</th>
                            <th>{{ _lang('Grade') }}</th>
                            <th>{{ _lang('Grade Range') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($examGrades as $examGrade)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input" name="checked_grades[]"
                                        value="{{ $examGrade->id }}" aria-label="Select SC-{{ $examGrade->id }}">
                                </td>
                                <td>{{ $examGrade->class?->class_name }}</td>
                                <td>
                                    <input type="text" class="form-control w_150"
                                        name="grades[{{ $examGrade->id }}][name]"
                                        value="{{ $examGrade->grade_name }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control w_150"
                                        name="grades[{{ $examGrade->id }}][range]"
                                        value="{{ $examGrade->grade_range }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fa fa-pencil" aria-hidden="true"></i>
                    Update
                </button>
            </form>

        </div>
    </div>
</div>

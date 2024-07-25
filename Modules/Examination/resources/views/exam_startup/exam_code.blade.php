<div class="my-2 row">
    <div class="col-lg-4">
        <div class="p-5 border table-responsive">
            <form method="post" action="{{ route('exam-code-store') }}" onsubmit="return validateForm1()">
                @csrf

                <div class="mb-3">
                    <label class="my-2 control-label">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id_select1" class="form-select form-select-sm form-select-solid"
                        data-control="select2" required>

                        {!! create_option('classes', 'id', 'class_name', request()->class_id) !!}
                    </select>
                </div>

                <div class="mb-3">
                    <label class="my-2 control-label">
                        <strong>Select Global Exam Code List</strong>
                        <span class="text-danger">*</span>
                    </label>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        <input type="checkbox" class="form-check-input" id="select-all1"
                                            aria-label="Select all">
                                    </th>
                                    <th scope="col">Code Title</th>
                                    <th scope="col">Total Marks</th>
                                    <th scope="col">Pass Mark</th>
                                    <th scope="col">Acceptance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($shortCodes as $shortCode)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input" name="selected_codes[]"
                                                value="{{ $shortCode->id }}"
                                                aria-label="Select SC-{{ $shortCode->id }}">
                                        </td>
                                        <td>{{ $shortCode->short_code_title }}</td>
                                        <td>{{ $shortCode->total_mark }}</td>
                                        <td>{{ $shortCode->pass_mark }}</td>
                                        <td>{{ $shortCode->accept_percent }}</td>
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
            <form action="{{ route('exam-code-update') }}" method="POST">
                @csrf
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase gs-0">
                            <th>
                                <input type="checkbox" class="form-check-input" id="select-all11"
                                    aria-label="Select all">
                            </th>
                            <th>{{ _lang('Class') }}</th>
                            <th>{{ _lang('Exam Code Title') }}</th>
                            <th>{{ _lang('Total Marks') }}</th>
                            <th>{{ _lang('Pass Marks') }}</th>
                            <th>{{ _lang('Acceptance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($examShortCodes as $examShortCode)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input" name="exam_code_ids[]"
                                        value="{{ $examShortCode->id }}"
                                        aria-label="Select SC-{{ $examShortCode->id }}">
                                </td>
                                <td>{{ $examShortCode->class?->class_name }}</td>
                                <td>
                                    <input type="text" class="form-control w_150"
                                        name="short_codes_title[{{ $examShortCode->id }}]"
                                        value="{{ $examShortCode->short_code_title }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control w_150"
                                        name="total_marks[{{ $examShortCode->id }}]"
                                        value="{{ $examShortCode->total_mark }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control w_150"
                                        name="pass_marks[{{ $examShortCode->id }}]"
                                        value="{{ $examShortCode->pass_mark }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control w_150"
                                        name="acceptance[{{ $examShortCode->id }}]"
                                        value="{{ $examShortCode->accept_percent }}">
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

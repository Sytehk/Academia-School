<div class="row my-2">
    <div class="col-lg-4">
        <div class="card-body table-responsive border p-5">
            <form method="post" action="{{ route('exam-assign-store') }}" onsubmit="return validateForm3()">
                @csrf

                <div class="mb-3">
                    <label class="control-label my-2">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id_select3" class="form-select form-select-sm form-select-solid"
                        data-control="select2" required>
                        {!! create_option('classes', 'id', 'class_name', request()->class_id) !!}
                    </select>
                </div>

                <div class="mb-3">
                    <label class="control-label my-2">Exam <span class="text-danger">*</span></label>
                    <select name="exam_ids[]" class="form-select form-select-sm form-select-solid"
                        data-control="select2" multiple>
                        {!! create_option('exams', 'id', 'name', request()->exam_id) !!}
                    </select>
                </div>

                <div class="mb-3">
                    <label class="control-label my-2">Merit Process Type <span class="text-danger">*</span></label>
                    <select name="merit_process_type_id" class="form-select form-select-sm form-select-solid"
                        data-control="select2" multiple>
                        {!! create_option('merit_process_types', 'id', 'type', request()->merit_process_type_id) !!}
                    </select>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card-body table-responsive border p-5">
            <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                <!--begin::Table head-->
                <thead>
                    <!--begin::Table row-->
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th>Class</th>
                        <th>Exam Name</th>
                        <th>Merit Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($classExams as $classExam)
                        <tr>
                            <td>{{ $classExam->class->class_name }}</td>
                            <td>{{ $classExam->exam->name ?? 'N/A' }}</td>
                            <td>{{ $classExam->meritType->type ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

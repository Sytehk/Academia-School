<div class="my-2 row justify-content-center">
    <div class="col-lg-8">
        <div class="border card-body">
            <div class="p-5 border table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($classes as $data)
                            <tr>
                                <td>{{ $data->class_name }}</td>
                                <td>
                                    <a href="#" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#modal_{{ $data->id }}">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach ($classes as $class)
    <!-- Modal for this class -->
    <div class="modal fade" id="modal_{{ $class->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>
                        {{ _lang('Grand Final Mark Update Class -') }}
                        <strong class="text-danger">{{ $class->class_name }}</strong>
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body scroll-y">
                    <form id="kt_modal_new_card_form_{{ $class->id }}" class="form"
                        action="{{ route('grand-final-exam-store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $class->id }}">

                        @php
                            $grandFinalSetting = Modules\Examination\Models\GrandFinalSetting::where(
                                'class_id',
                                $class->id,
                            )->first();
                            $calculationMethod = $grandFinalSetting->calculation_method ?? 'weighted_percentage';
                        @endphp

                        <div class="mb-4 form-group">
                            <label class="form-label fw-bold">{{ _lang('Calculation Method') }}</label>
                            <select class="form-select form-select-solid calculation-method-select"
                                name="calculation_method" id="calculation_method_{{ $class->id }}">
                                <option value="weighted_percentage"
                                    {{ $calculationMethod == 'weighted_percentage' ? 'selected' : '' }}>
                                    {{ _lang('Weighted Percentage (weights must total 100%)') }}
                                </option>
                                <option value="full_marks" {{ $calculationMethod == 'full_marks' ? 'selected' : '' }}>
                                    {{ _lang('Full Marks (sum of raw marks obtained across exams)') }}
                                </option>
                            </select>
                            <div class="form-text">
                                {{ _lang('Full Marks mode ignores the percentage column below and simply adds each exam\'s obtained marks together.') }}
                            </div>
                        </div>

                        <table class="table align-middle table-bordered">
                            <thead class="border table-light">
                                <tr>
                                    <th style="padding-left: 10px;">Exam Name</th>
                                    <th>Percentage</th>
                                    <th>Exam Serial</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $grandFinalExams = Modules\Examination\Models\GrandFinalClassExam::where(
                                        'class_id',
                                        $class->id,
                                    )->get();
                                    $classExamsData = Modules\Examination\Models\ClassExam::with('exam')
                                        ->where('class_id', $class->id)
                                        ->get();
                                    // Serial must cover however many exams this class actually has —
                                    // a fixed 5/10 option list left institutes with more class tests
                                    // or model tests unable to give every exam a serial (BUG-008).
                                    $maxSerial = max(10, $grandFinalExams->count(), $classExamsData->count());
                                @endphp

                                @if ($grandFinalExams->isNotEmpty())
                                    @foreach ($grandFinalExams as $grandFinalExam)
                                        <tr>
                                            <td style="padding-left: 10px;">
                                                {{ $grandFinalExam->exam->name ?? 'Unknown Exam' }}
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    class="form-control percentage-input"
                                                    value="{{ $grandFinalExam->percentage }}" max="100"
                                                    min="0" name="percentages[{{ $grandFinalExam->exam_id }}]"
                                                    {{ $calculationMethod == 'weighted_percentage' ? 'required' : '' }}>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm form-select-solid"
                                                    data-control="select2"
                                                    name="serial_no[{{ $grandFinalExam->exam_id }}]">
                                                    @for ($i = 1; $i <= $maxSerial; $i++)
                                                        <option value="{{ $i }}"
                                                            {{ $i == $grandFinalExam->serial_no ? 'selected' : '' }}>
                                                            {{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    @if ($classExamsData->isEmpty())
                                        <tr>
                                            <td colspan="3">{{ _lang('No exams found for this class.') }}</td>
                                        </tr>
                                    @else
                                        @foreach ($classExamsData as $classExam)
                                            <tr>
                                                <td style="padding-left: 10px;">{{ $classExam->exam?->name }}</td>
                                                <td>
                                                    <input type="number" step="0.01"
                                                        class="form-control percentage-input" value="0"
                                                        max="100" min="0"
                                                        name="percentages[{{ $classExam->exam_id }}]"
                                                        {{ $calculationMethod == 'weighted_percentage' ? 'required' : '' }}>
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm form-select-solid"
                                                        data-control="select2"
                                                        name="serial_no[{{ $classExam->exam_id }}]">
                                                        @for ($i = 1; $i <= $maxSerial; $i++)
                                                            <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>
                                                                {{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endif

                            </tbody>
                        </table>
                        <div class="modal-footer">
                            <button type="reset" id="kt_modal_new_card_cancel"
                                class="btn btn-light me-3">{{ _lang('Reset') }}</button>
                            <button type="submit" id="kt_modal_new_card_submit"
                                class="btn btn-primary">{{ _lang('Submit') }}</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endforeach

<script>
    $(document).on('change', '.calculation-method-select', function() {
        var $percentageInputs = $(this).closest('form').find('.percentage-input');
        if ($(this).val() === 'weighted_percentage') {
            $percentageInputs.attr('required', 'required');
        } else {
            $percentageInputs.removeAttr('required');
        }
    });
</script>

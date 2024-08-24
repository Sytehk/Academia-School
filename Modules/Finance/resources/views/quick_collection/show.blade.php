@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Quick Collection') }}
@endsection
<style>
    /* Custom CSS to remove borders */
    .no-border tr,
    .no-border td {
        border: none !important;
    }

    .card {
        padding: 0px !important;
    }

    .table-responsive th,
    .table-responsive td {
        font-size: 11px !important;
        padding: 4px !important;
    }

    .table-responsive input {
        padding: 10px !important;
    }

    .academic-section {
        background-color: #f4f3ef;
        border-radius: 10px;
        padding: 10px;
    }

    .sub_due_column {
        display: block;
        padding-top: 12px;
        font-size: 14px;
        margin: 0 auto;
        text-align: center;
    }


    table {
        font-size: 14px !important;
        line-height: 8px !important;
    }

    .card-img,
    .card-img-bottom,
    .card-img-top {
        width: 150px !important;
        height: 150px !important;
        margin: 0 auto !important;
        padding-bottom: 10px;
    }
</style>

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-6">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td><strong>Student's Name</strong></td>
                                <td>
                                    <span>:</span>
                                    @isset($studentSession->student->first_name)
                                        {{ $studentSession->student->first_name . ' ' . ($studentSession->student->last_name ?? '') }}
                                    @endisset
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Class</strong></td>
                                <td>
                                    <span>:</span>
                                    @isset($studentSession->class->class_name)
                                        {{ $studentSession->class->class_name }}
                                    @endisset
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Section</strong></td>
                                <td>
                                    <span>:</span>
                                    @isset($studentSession->section->section_name)
                                        {{ $studentSession->section->section_name }}
                                    @endisset
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Group</strong></td>
                                <td>
                                    <span>:</span>
                                    @isset($studentSession->group?->group_name)
                                        {{ $studentSession->group->group_name }}
                                    @endisset
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Student Roll</strong></td>
                                <td>
                                    <span>:</span>
                                    @isset($studentSession->roll)
                                        {{ $studentSession->roll }}
                                    @endisset
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Father's Name</strong></td>
                                <td>
                                    <span>:</span>
                                    @isset($studentSession->student->father_name)
                                        {{ $studentSession->student->father_name }}
                                    @endisset
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Mother's Name</strong></td>
                                <td>
                                    <span>:</span>
                                    @isset($studentSession->student->mother_name)
                                        {{ $studentSession->student->mother_name }}
                                    @endisset
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Category</strong></td>
                                <td>
                                    <span>:</span>
                                    @isset($studentSession->student->studentCategory->name)
                                        {{ $studentSession->student->studentCategory->name }}
                                    @endisset
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Mobile No.</strong></td>
                                <td>
                                    <span>:</span>
                                    @isset($studentSession->student->phone)
                                        {{ $studentSession->student->phone }}
                                    @endisset
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-lg-6">
                    <div class="row">
                        <div class="col-lg-8 academic-section m-0 p-0 border">
                            <div class="card border-primary p-2">
                                <div class="card-header justify-content-center">
                                    <h5>{{ _lang('Search Student') }}</h5>
                                </div>
                                <div class="card-body m-0 p-0">
                                    <form method="get" action="{{ route('quick-collection-student') }}">

                                        <input type="hidden" name="class_id" value="{{ $studentSession->class_id }}">
                                        <input type="hidden" name="section_id" value="{{ $studentSession->section_id }}">

                                        <div class="row">

                                            {{-- Class --}}
                                            <div class="col-lg-4 mb-3">
                                                <label class="fw-bold">Class</label>
                                                <span class="badge bg-primary fs-6 px-3 py-2">
                                                    {{ $studentSession->class->class_name ?? 'N/A' }}
                                                </span>
                                            </div>

                                            {{-- Section --}}
                                            <div class="col-lg-4 mb-3">
                                                <label class="fw-bold">Section</label>
                                                <span class="badge bg-success fs-6 px-3 py-2">
                                                    {{ $studentSession->section->section_name ?? 'N/A' }}
                                                </span>
                                            </div>

                                            {{-- Group --}}
                                            <div class="col-lg-4 mb-3">
                                                <label class="fw-bold">Group</label>
                                                <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                                    {{ $studentSession->group?->group_name ?? 'N/A' }}
                                                </span>
                                            </div>

                                            {{-- Roll Input --}}
                                            <div class="col-lg-12 mt-3">
                                                <label class="control-label my-2">{{ _lang('Student Roll') }}</label>
                                                <input class="form-control" type="number" name="student_roll"
                                                    value="{{ $studentSession->roll }}" placeholder="Student Roll"
                                                    required />
                                            </div>

                                        </div>

                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                Search
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-primary mb-3">
                                <div class="card-header text-center">
                                    @if ($studentSession->student->user?->image)
                                        <img src="{{ asset('storage/users/' . $studentSession->student->user->image) }}"
                                            class="card-img-top" alt="" />
                                    @else
                                        <img src="{{ asset('images/no-image.png') }}" class="card-img-top"
                                            alt="" />
                                    @endif
                                </div>
                                <div class="card-body text-center m-0 p-0">
                                    Roll No :
                                    <span class="badge bg-danger fs-6 px-3 py-2">
                                        {{ $studentSession->roll ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @isset($studentSession->student->status)
                @if ((int) $studentSession->student->status === 1)
                    <form method="post" action="{{ route('quick-collection.store') }}">
                        @csrf

                        <input type="hidden" type="number" name="student_id" value="{{ $studentSession->student->id }}" />

                        <div class="row my-5">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                                        <!--begin::Table head-->
                                        <thead class="border">
                                            <!--begin::Table row-->
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th>
                                                    <input type="checkbox" id="checkbox-all" />
                                                </th>
                                                <th>
                                                    <b>{{ _lang('Fee Head') }}</b>
                                                </th>
                                                <th>{{ _lang('Select Fee Sub Head') }}</th>
                                                {{-- <th>{{ _lang('Total Due') }}</th> --}}
                                                <th>{{ _lang('Total Paid') }}</th>
                                                <th>{{ _lang('Waiver') }}</th>
                                                <th>{{ _lang('Fine Payable') }}</th>
                                                <th>{{ _lang('Fee Payable') }}</th>
                                                <th>{{ _lang('Fee & Fine Payable') }}</th>
                                                <th>{{ _lang('Prev Due Payable') }}</th>
                                                <th>{{ _lang('Prev Due Paid') }}</th>
                                                <th>{{ _lang('Total Payable') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($feeHeads as $feeHead)
                                                @if (isset($feeHead->feeSubHeads) && count($feeHead->feeSubHeads) > 0)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox"
                                                                name="fee_heads[{{ $feeHead->id }}][fee_head_id]"
                                                                value="{{ $feeHead->id }}" class="fee-head-id" />
                                                        </td>
                                                        <td>
                                                            <b>{{ $feeHead->name }}</b>
                                                        </td>
                                                        <td>
                                                            <input type="hidden" name="fee_sub_head_id"
                                                                value="{{ $feeHead->id }}" />
                                                            <select name="fee_heads[{{ $feeHead->id }}][sub_head_ids][]"
                                                                id="sub_head_id_{{ $feeHead->id }}"
                                                                class="sub_head_ids form-select form-select-sm form-select-solid"
                                                                multiple
                                                                onchange="getFeeAmount(this.value, {{ $feeHead->id }})"
                                                                data-control="select2" data-placeholder="Select Sub Head">
                                                                <option value="">{{ _lang('--Select--') }}</option>
                                                                @foreach ($feeHead->feeSubHeads as $subHead)
                                                                    <option value="{{ $subHead->id }}">
                                                                        {{ $subHead->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        {{-- <td>
                                                            <span id="sub_head_due_amount_{{ $feeHead->id }}"
                                                                class="sub_due_column">
                                                                0
                                                            </span>
                                                        </td> --}}
                                                        <td>
                                                            <input type="number" class="form-control p-2"
                                                                name="fee_heads[{{ $feeHead->id }}][total_paid]"
                                                                id="total_paid_{{ $feeHead->id }}" min="0" />
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control p-2"
                                                                name="fee_heads[{{ $feeHead->id }}][waiver]"
                                                                id="waiver_{{ $feeHead->id }}" min="0" readonly />
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control p-2"
                                                                name="fee_heads[{{ $feeHead->id }}][fine_payable]"
                                                                id="fine_payable_{{ $feeHead->id }}" readonly />
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control p-2"
                                                                name="fee_heads[{{ $feeHead->id }}][fee_payable]"
                                                                id="fee_payable_{{ $feeHead->id }}" readonly />
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control p-2"
                                                                name="fee_heads[{{ $feeHead->id }}][fee_and_fine_payable]"
                                                                id="fee_and_fine_payable_{{ $feeHead->id }}" readonly />
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control p-2"
                                                                name="fee_heads[{{ $feeHead->id }}][previous_due_payable]"
                                                                id="previous_due_payable_{{ $feeHead->id }}" readonly />
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control p-2"
                                                                name="fee_heads[{{ $feeHead->id }}][previous_due_paid]"
                                                                id="previous_due_paid_{{ $feeHead->id }}" readonly />
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control p-2"
                                                                name="fee_heads[{{ $feeHead->id }}][total_payable]"
                                                                id="total_payable_{{ $feeHead->id }}" readonly />
                                                        </td>

                                                    </tr>
                                                @endif
                                            @empty
                                                <tr>
                                                    <td colspan="12" class="text-center">
                                                        <h6>
                                                            {{ _lang('No Fee Head Found. Please create or configure fee head first.') }}
                                                        </h6>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 30px">
                            <div class="col-lg-6">
                                <div class="row justify-content-center">
                                    <div class="col-lg-6">
                                        <div class="card text-center bg-light my-5 border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="p-3 mx-auto">
                                                    {{ _lang('Attendance Fine') }}
                                                </h6>
                                            </div>
                                            <div class="card-body py-5">
                                                <p class="card-text" id="attendance_fine_print">
                                                    {{ format_currency(0) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="card text-center bg-light my-5 border-secondary">
                                            <div class="card-header bg-secondary text-white">
                                                <h6 class="p-3 mx-auto">
                                                    {{ _lang('Quiz Fine') }}
                                                </h6>
                                            </div>
                                            <div class="card-body py-5">
                                                <p class="card-text" id="quiz_fine_print">
                                                    {{ format_currency(0) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="card text-center bg-light my-5 border-info">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="p-3 mx-auto">
                                                    {{ _lang('Lab Fine') }}
                                                </h6>
                                            </div>
                                            <div class="card-body py-5">
                                                <p class="card-text" id="lab_fine_print">
                                                    {{ format_currency(0) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="attendance_fine" id="attendance_fine" />
                                    <input type="hidden" name="quiz_fine" id="quiz_fine" />
                                    <input type="hidden" name="lab_fine" id="lab_fine" />
                                    <div class="col-lg-6">
                                        <div class="card text-center bg-light my-5 border-warning">
                                            <div class="card-header bg-warning text-white">
                                                <h6 class="p-3 mx-auto">{{ _lang('Total Paid') }}</h6>
                                            </div>
                                            <div class="card-body py-5">
                                                <input type="hidden" name="total_paid" id="total_paid" />
                                                <p class="card-text" id="total_paid_print">
                                                    {{ format_currency(0) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="card text-center bg-light my-5 border-success">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="p-3 mx-auto">{{ _lang('Total Payable') }}</h6>
                                            </div>
                                            <div class="card-body py-5">
                                                <input type="hidden" name="total_payable" id="total_payable" />
                                                <p class="card-text" id="total_payable_print">
                                                    {{ format_currency(0) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="card text-center bg-light mt-5 border-info">
                                            <div class="card-header bg-success text-white"
                                                style="margin-bottom: 0 !important;">
                                                <h6 class="p-3 mx-auto">{{ _lang('TC Amount') }}</h6>
                                            </div>
                                            <div class="card-body m-0 p-0">
                                                {{ get_option('currency_symbol') ?: '$' }} <input type="number" name="tc_amount" id="tc_amount"
                                                    style="border: 1px solid #fbf8f8; width: 120px; padding-left: 15px; height: 40px; margin: 10px;"
                                                    value="{{ old('tc_amount') }}" readonly />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 mt-2">
                                <div class="card-header my-3 bg-primary text-white">
                                    <div class="card-body p-4">
                                        <input type="checkbox" class="form-check-input mx-2" id="attendanceFineGet"
                                            name="attendanceFineGet">
                                        <label class="control-label">Attendance
                                            Fine</label>
                                        <input type="hidden" id="studentId" value="{{ $studentSession->student->id }}">
                                    </div>

                                    <input type="hidden" id="selected_month" name="selected_month">

                                </div>

                                <div class="card-header my-3 bg-secondary text-white">
                                    <div class="card-body p-4">
                                        <input type="checkbox" class="form-check-input mx-2" id="quizFineGet"
                                            name="quizFineGet">
                                        <label class="control-label">Quiz Fine</label>
                                    </div>
                                    <input type="hidden" id="selected_month_quiz" name="selected_month_quiz">
                                </div>

                                <div class="card-header my-3 bg-info text-white">
                                    <div class="card-body p-4">
                                        <input type="checkbox" class="form-check-input mx-2" id="labFineGet"
                                            name="labFineGet">
                                        <label class="control-label">Lab Fine</label>
                                    </div>

                                    <input type="hidden" id="selected_month_lab" name="selected_month_lab">

                                </div>

                                <div class="card-header my-3 bg-success text-white">
                                    <div class="card-body p-4">
                                        <input type="checkbox" class="form-check-input mx-2" id="selected_tc_amount"
                                            name="selected_tc_amount">
                                        <label class="control-label">TC Amount</label>
                                    </div>

                                    <input type="hidden" id="selected_tc_amount" name="selected_tc_amount">
                                </div>

                                <div class="form-group">
                                    <textarea class="form-control" type="text" name="note" cols="2" rows="5"
                                        placeholder="{{ _lang('Enter Notes (if any)') }}">{{ old('note') }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-3 mt-2">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Payment Date') }}</label>
                                    <input class="form-control" type="text" name="date"
                                        value="{{ old('date') ?? now()->format('Y-m-d') }}" required />
                                </div>

                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Paid By') }}</label>
                                    <select class="form-control" name="ledger_id" required>
                                        @foreach ($cashLedgers as $cashLedger)
                                            <option value="{{ $cashLedger->id }}"
                                                {{ old('ledger_id') == $cashLedger->id ? 'selected' : '' }}>
                                                {{ $cashLedger->ledger_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mt-4">
                                    @include('partials.notify-channel', ['notifyChannel' => 'none', 'label' => 'Notify via'])
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-success mt-5">
                                        {{ _lang('Process to Collection') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif
            @endisset

            @isset($studentSession->student->status)
                @if ((int) $studentSession->student->status === 0)
                    <div class="row p-4">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h2 class="text-danger font-weight-bold">Attention</h2>
                                    <p class="text-danger mt-3 mb-3" style="font-size: 1.2em;">
                                        This student is scheduled to be issued a Transfer Certificate (TC).
                                    </p>
                                    <p class="text-muted" style="font-size: 1em;">
                                        Please review the student's details and take any necessary actions promptly.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endisset
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        function formatCurrencyJs(amount) {
            const symbol = @json(get_option('currency_symbol') ?: '$');
            const position = @json(get_option('currency_position') ?: 'left');
            const formatted = Number(amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            return position === 'right' ? formatted + symbol : symbol + formatted;
        }

        // On click checkbox-all, check all checkboxes by class fee-head-id
        $("#checkbox-all").click(function() {
            if ($(this).is(":checked")) {
                $(".fee-head-id").prop("checked", true);
            } else {
                $(".fee-head-id").prop("checked", false);
            }

            getSummaryAmount();
        });

        // on change chekbox fee-head-id, we'll call run the function getSummaryAmount()
        $(".fee-head-id").change(function() {
            getSummaryAmount();
        });

        // on change any value of input, we'll call run the function getSummaryAmount()
        $("input").change(function() {
            getSummaryAmount();
        });

        // on click fee-sub-head-id, we'll call the API to get the fee amounts.

        function getFeeAmount(subHeadId, feeHeadId) {
            var selectElements = $(".sub_head_ids");
            var allSubHeadIds = [];
            selectElements.each(function(index, selectElement) {
                var subHeadIds = $(selectElement).val() || [];
                allSubHeadIds = allSubHeadIds.concat(subHeadIds);
            });
            $.ajax({
                url: "{{ route('student-collection.sub-head-wise-calculation') }}",
                type: "POST",
                data: {
                    fee_sub_head_ids: allSubHeadIds,
                    fee_head_id: feeHeadId,
                    student_id: "{{ $studentSession->student->id }}",
                    _token: "{{ csrf_token() }}",
                },
                success: function(response) {
                    // Fill table data.
                    $("#sub_head_due_amount_" + feeHeadId).html(response.due_amount);
                    $("#total_paid_" + feeHeadId).val(response.total_paid);
                    $("#waiver_" + feeHeadId).val(response.waiver);
                    $("#fine_payable_" + feeHeadId).val(response.fine_payable);
                    $("#fee_payable_" + feeHeadId).val(response.fee_payable);
                    $("#fee_and_fine_payable_" + feeHeadId).val(
                        response.fee_and_fine_payable
                    );
                    $("#previous_due_payable_" + feeHeadId).val(
                        response.previous_due_payable
                    );
                    $("#previous_due_paid_" + feeHeadId).val(response.previous_due_paid);
                    $("#total_payable_" + feeHeadId).val(response.total_payable);

                    // Fill totals data.
                    getSummaryAmount();
                },
            });
        }

        function getSummaryAmount() {
            var totalPaid = 0;
            var totalPayable = 0;
            var totalDue = 0;

            var attendance_fine = $("#attendance_fine").val();
            var quiz_fine = $("#quiz_fine").val();
            var lab_fine = $("#lab_fine").val();
            var tc_amount = $("#tc_amount").val();

            if (attendance_fine) {
                totalPaid = totalPaid + parseFloat(attendance_fine);
                totalPayable = totalPayable + parseFloat(attendance_fine);
            }
            if (quiz_fine) {
                totalPaid = totalPaid + parseFloat(quiz_fine);
                totalPayable = totalPayable + parseFloat(quiz_fine);
            }
            if (lab_fine) {
                totalPaid = totalPaid + parseFloat(lab_fine);
                totalPayable = totalPayable + parseFloat(lab_fine);
            }
            if (tc_amount) {
                totalPaid = totalPaid + parseFloat(tc_amount);
                totalPayable = totalPayable + parseFloat(tc_amount);
            }
            $(".fee-head-id").each(function() {
                if ($(this).is(":checked")) {
                    var feeHeadId = $(this).val();
                    var feeHeadId = $(this).val();
                    var paidAmount = $("#total_paid_" + feeHeadId).val();
                    var waiver = $("#waiver_" + feeHeadId).val();
                    var totalPayableAmount = $("#total_payable_" + feeHeadId).val();
                    var dueAmount = $("#sub_head_due_amount_" + feeHeadId).html();

                    totalPaid += paidAmount ? parseFloat(paidAmount) : 0;
                    totalPayable += totalPayableAmount ? parseFloat(totalPayableAmount) : 0;
                    totalDue += dueAmount ? parseFloat(dueAmount) : 0;
                }
            });
            $("#total_paid").val(totalPaid);
            $("#total_paid_print").html(formatCurrencyJs(totalPaid));
            $("#total_payable").val(totalPayable);
            $("#total_payable_print").html(formatCurrencyJs(totalPayable));
            $("#total_due").val(totalDue);
            $("#total_due_print").html(formatCurrencyJs(totalDue));
        }

        $('#attendanceFineGet').change(function() {
            if (this.checked) {
                const studentId = $('#studentId').val();

                // Make the AJAX call
                $.ajax({
                    url: '/get-attendance-fine-amount/' + studentId, // Adjust this URL to your API endpoint
                    type: 'GET',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $("#attendance_fine").val(response);
                        $("#selected_month").val(response);

                        $("#attendance_fine_print").html(formatCurrencyJs(response));
                        getSummaryAmount();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            } else {
                $("#attendance_fine").val(0);
                $("#selected_month").val(0);
                $("#attendance_fine_print").html(formatCurrencyJs(0));
                getSummaryAmount();
            }
        });

        $('#quizFineGet').change(function() {
            if (this.checked) {
                const studentId = $('#studentId').val();

                // Make the AJAX call
                $.ajax({
                    url: '/get-quiz-fine-amount/' + studentId, // Adjust this URL to your API endpoint
                    type: 'GET',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $("#quiz_fine").val(response);
                        $("#selected_month_quiz").val(response);
                        $("#quiz_fine_print").html(formatCurrencyJs(response));
                        getSummaryAmount();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            } else {
                $("#quiz_fine").val(0);
                $("#selected_month_quiz").val(0);
                $("#quiz_fine_print").html(formatCurrencyJs(0));
                getSummaryAmount();
            }
        });

        $('#labFineGet').change(function() {
            if (this.checked) {
                const studentId = $('#studentId').val();

                // Make the AJAX call
                $.ajax({
                    url: '/get-lab-fine-amount/' + studentId, // Adjust this URL to your API endpoint
                    type: 'GET',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $("#lab_fine").val(response);
                        $("#selected_month_lab").val(response);
                        $("#lab_fine_print").html(formatCurrencyJs(response));
                        getSummaryAmount();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            } else {
                $("#lab_fine").val(0);
                $("#selected_month_lab").val(0);
                $("#lab_fine_print").html(formatCurrencyJs(0));
                getSummaryAmount();
            }
        });

        $('#selected_tc_amount').change(function() {
            if (this.checked) {
                // Make the AJAX call
                $.ajax({
                    url: '/get-tc-amount',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $("#tc_amount").val(response);
                        getSummaryAmount();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            } else {
                $("#tc_amount").val(0);
                getSummaryAmount();
            }
        });

        $('#tc_amount').on('keydown', function() {
            getSummaryAmount();
        });

        // Add event listener for when the total paid input field loses focus
        $('[id^="total_paid_"]').on('input', function() {
            var feeHeadId = $(this).attr('id').split('_')[2];
            var totalPaid = parseFloat($(this).val());
            var totalPayable = parseFloat($('#total_payable_' + feeHeadId).val());

            if (totalPaid > totalPayable) {
                alert('Total paid amount cannot exceed total payable amount.');
                $(this).val(totalPayable);
            }
        });
    </script>
@endsection

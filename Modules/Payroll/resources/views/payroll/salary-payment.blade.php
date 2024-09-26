@extends('layouts.backend')

<style>
    .payroll_salary_input {
        width: 100px !important;
        border: none !important;
    }

    .editable-input {
        border: 1px solid #3498db;
        background-color: #fff;
        color: #333;
    }

    .readonly-input {
        border: none;
        background-color: transparent;
        color: #777;
    }

    .table th {
        width: 90px;
    }
</style>

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">{{ _lang('Salary Payment') }}</span>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <form method="POST" action="{{ route('salary.payment-process.search') }}">
                            @csrf
                            <div class="col-md-2">
                                <select class="form-select" name="year">
                                    <option value="">--Select Year--</option>
                                    <option {{ $year == 2023 ? 'selected' : '' }} value="2023">2023</option>
                                    <option {{ $year == 2024 ? 'selected' : '' }} value="2024">2024</option>
                                    <option {{ $year == 2025 ? 'selected' : '' }} value="2025">2025</option>
                                    <option {{ $year == 2026 ? 'selected' : '' }} value="2026">2026</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <select class="form-select select2" name="month">
                                    <option value="">--Select Month--</option>
                                    <option {{ $month == 1 ? 'selected' : '' }} value="1">Jan</option>
                                    <option {{ $month == 2 ? 'selected' : '' }} value="2">Feb</option>
                                    <option {{ $month == 3 ? 'selected' : '' }} value="3">Mar</option>
                                    <option {{ $month == 4 ? 'selected' : '' }} value="4">Apr</option>
                                    <option {{ $month == 5 ? 'selected' : '' }} value="5">May</option>
                                    <option {{ $month == 6 ? 'selected' : '' }} value="6">Jun</option>
                                    <option {{ $month == 7 ? 'selected' : '' }} value="7">Jul</option>
                                    <option {{ $month == 8 ? 'selected' : '' }} value="8">Aug</option>
                                    <option {{ $month == 9 ? 'selected' : '' }} value="9">Sep</option>
                                    <option {{ $month == 10 ? 'selected' : '' }} value="10">Oct</option>
                                    <option {{ $month == 11 ? 'selected' : '' }} value="11">Nov</option>
                                    <option {{ $month == 12 ? 'selected' : '' }} value="12">Dec</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success">{{ _lang('Search') }}</button>
                            </div>
                        </form>
                    </div>

                    @if (isset($users) && isset($salaryHeads))
                        <form method="POST" action="{{ route('salary.payment-process.create') }}" id="salary-form"
                            class="font-size-10">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <thead>
                                        <tr>
                                            <th style="width: 90px;">
                                                <input type="checkbox" id="select-all">
                                            </th>
                                            <th style="width: 90px;">{{ _lang('Name') }}</th>
                                            <th>{{ _lang('Designation') }}</th>

                                            @foreach ($salaryHeads as $salaryHead)
                                                <th>{{ __($salaryHead->name) }}
                                                    ({{ $salaryHead->type == 'Addition' ? '+' : ($salaryHead->type == 'Deduction' ? '-' : '') }})
                                                </th>
                                            @endforeach

                                            <th>{{ _lang('Due') }}</th>
                                            <th>{{ _lang('Net Salary') }}</th>
                                            <th>{{ _lang('Payable') }}</th>
                                            <th>{{ _lang('Paid') }}</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($users as $user)
                                            @if (isset($user->userPayroll) && $user->userPayroll)
                                                @if (isset($user->payslipSalary) && $user->payslipSalary)
                                                    @foreach ($user->payslipSalary as $payslipSalary)
                                                        @if ($payslipSalary->year == $year && $payslipSalary->month == $month && $payslipSalary->is_paid !== '1')
                                                            <tr>
                                                                <td style="width: 90px;">
                                                                    <input type="checkbox" class="salary-checkbox"
                                                                        name="user_id[]" value="{{ $user->id }}">
                                                                </td>
                                                                <td>{{ $user->name }}</td>
                                                                <td>{{ $user->user_type }}</td>

                                                                @php
                                                                    $userPayrollId = $user->userPayroll->id;
                                                                    $userSalaryHeadUserPayrolls = array_filter(
                                                                        $salaryHeadUserPayrolls,
                                                                        function ($item) use ($userPayrollId) {
                                                                            return $item->user_payroll_id ===
                                                                                $userPayrollId;
                                                                        },
                                                                    );
                                                                @endphp

                                                                @foreach ($userSalaryHeadUserPayrolls as $payrollSalaryHead)
                                                                    <td>
                                                                        <input class="payroll_salary_input readonly-input"
                                                                            value="{{ $payrollSalaryHead->amount }}"
                                                                            readonly />
                                                                    </td>
                                                                @endforeach

                                                                @php
                                                                    $duAmount = 0;
                                                                    $payableAmount = 0;
                                                                    $duAmount =
                                                                        $user->userPayroll->current_due -
                                                                        $user->userPayroll->current_advance;
                                                                    $payableAmount =
                                                                        $user->userPayroll->net_salary + $duAmount;
                                                                @endphp

                                                                <td>
                                                                    <input name="due[]"
                                                                        class="payroll_salary_input readonly-input"
                                                                        value="{{ $duAmount ?? 0 }}" readonly>
                                                                </td>
                                                                <td>
                                                                    <input class="payroll_salary_input readonly-input"
                                                                        name="net_salary[]"
                                                                        value="{{ $user->userPayroll->net_salary ?? 0 }}"
                                                                        readonly>
                                                                </td>
                                                                <td>
                                                                    <input name="payable[]"
                                                                        class="payroll_salary_input readonly-input"
                                                                        value="{{ $payableAmount ?? 0 }}" readonly>
                                                                </td>
                                                                <td>
                                                                    <input name="paid_amount[]"
                                                                        class="payroll_salary_input readonly-input"
                                                                        value="0" max="{{ $payableAmount ?? 0 }}">
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="float-end">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-cog" aria-hidden="true"></i>
                                    {{ _lang('Process') }}
                                </button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                    {{ _lang('Delete') }}
                                </button>
                                <button type="submit" class="btn btn-info">
                                    <i class="fa fa-cloud-download" aria-hidden="true"></i>
                                    {{ _lang('Download PDF') }}
                                </button>
                            </div>

                            <div style="clear: both;"></div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        $(document).ready(function() {
            $("#select-all").click(function() {
                var isChecked = $(this).prop("checked");
                $(".salary-checkbox").prop("checked", isChecked);
            });

            $(".salary-checkbox").click(function() {
                var allChecked = $(".salary-checkbox:checked").length === $(".salary-checkbox").length;
                $("#select-all").prop("checked", allChecked);
            });

            var form = document.getElementById('salary-form');
            form.addEventListener('submit', function(event) {
                var atLeastOneChecked = [...document.querySelectorAll('.salary-checkbox')].some(checkbox =>
                    checkbox.checked);
                if (!atLeastOneChecked) {
                    event.preventDefault();
                    alert('Please select at least one salary.');
                }
            });
        });
    </script>
@endsection

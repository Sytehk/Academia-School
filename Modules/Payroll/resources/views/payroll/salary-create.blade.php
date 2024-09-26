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

    .readonly-input-net-salary {
        border: none;
        background-color: transparent;
        color: #777;
    }
</style>
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ _lang('Create Salary Slip') }}</h4>
                </div>

                <div class="panel-body">
                    <div class="row" style="margin-bottom: 15px; margin-left: 0px;">
                        <form method="POST" action="{{ route('salary.create.search') }}">
                            @csrf
                            <div class="col-md-2">
                                <select class="form-control" name="year">
                                    <option value="">--Select Year--</option>
                                    <option {{ isset($year) && $year == '2024' ? 'selected' : '' }} value="2024">2024
                                    </option>
                                    <option {{ isset($year) && $year == '2025' ? 'selected' : '' }} value="2025">2025
                                    </option>
                                    <option {{ isset($year) && $year == '2026' ? 'selected' : '' }} value="2026">2026
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <select class="form-control" name="month">
                                    <option value="">--Select Month--</option>
                                    <option {{ isset($month) && $month == '1' ? 'selected' : '' }} value="1">Jan
                                    </option>
                                    <option {{ isset($month) && $month == '2' ? 'selected' : '' }} value="2">Feb
                                    </option>
                                    <option {{ isset($month) && $month == '3' ? 'selected' : '' }} value="3">Mar
                                    </option>
                                    <option {{ isset($month) && $month == '4' ? 'selected' : '' }} value="4">Apr
                                    </option>
                                    <option {{ isset($month) && $month == '5' ? 'selected' : '' }} value="5">May
                                    </option>
                                    <option {{ isset($month) && $month == '6' ? 'selected' : '' }} value="6">Jun
                                    </option>
                                    <option {{ isset($month) && $month == '7' ? 'selected' : '' }} value="7">Jul
                                    </option>
                                    <option {{ isset($month) && $month == '8' ? 'selected' : '' }} value="8">Aug
                                    </option>
                                    <option {{ isset($month) && $month == '9' ? 'selected' : '' }} value="9">Sep
                                    </option>
                                    <option {{ isset($month) && $month == '10' ? 'selected' : '' }} value="10">Oct
                                    </option>
                                    <option {{ isset($month) && $month == '11' ? 'selected' : '' }} value="11">Nov
                                    </option>
                                    <option {{ isset($month) && $month == '12' ? 'selected' : '' }} value="12">Dec
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success">{{ _lang('Search') }}</button>
                            </div>
                        </form>
                    </div>

                    @if (isset($year) && isset($month))
                        <form method="POST" action="{{ route('salary.create.store') }}" id="salary-form">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <thead>
                                        <th>
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <td>{{ _lang('Name') }}</td>
                                        <th>{{ _lang('Designation') }}</th>
                                        <th>{{ _lang('Net Salary') }}</th>
                                        @foreach ($salaryHeads as $salaryHead)
                                            <th>{{ __($salaryHead->name) }}
                                                ({{ $salaryHead->type == 'Addition' ? '+' : ($salaryHead->type == 'Deduction' ? '-' : '') }})
                                            </th>
                                        @endforeach
                                    </thead>

                                    <tbody>
                                        @foreach ($users as $user)
                                            @if (isset($user->userPayroll) && $user->userPayroll)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="salary-checkbox" name="user_id[]"
                                                            value="{{ $user->id }}">
                                                    </td>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->user_type }}</td>
                                                    <td>
                                                        <input class="payroll_salary_input readonly-input-net-salary"
                                                            value="{{ $user->userPayroll->net_salary ?? 0 }}" disabled>
                                                    </td>

                                                    @php
                                                        $userPayrollId = $user->userPayroll->id;
                                                        $userSalaryHeadUserPayrolls = array_filter(
                                                            $salaryHeadUserPayrolls,
                                                            function ($item) use ($userPayrollId) {
                                                                return $item->user_payroll_id === $userPayrollId;
                                                            },
                                                        );
                                                    @endphp

                                                    @foreach ($userSalaryHeadUserPayrolls as $payrollSalaryHead)
                                                        <td>
                                                            <input class="payroll_salary_input readonly-input"
                                                                name="payroll_heads[{{ $user->id }}][{{ $payrollSalaryHead->user_payroll_id }}][{{ $payrollSalaryHead->salary_head_id }}]"
                                                                value="{{ $payrollSalaryHead->amount }}" readonly />
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-primary"
                                style="margin-left: 1%">{{ _lang('Create') }}</button>
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
                var atLeastOneChecked = [...document.querySelectorAll('.salary-checkbox')].some(
                    checkbox => checkbox.checked);
                if (!atLeastOneChecked) {
                    event.preventDefault();
                    alert('Please select at least one salary.');
                }
            });
        });
    </script>
@endsection

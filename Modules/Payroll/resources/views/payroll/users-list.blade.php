@extends('layouts.backend')

<style>
    .payroll_salary_input {
        width: 100px !important;
        border: none !important;
    }

    .editable-input {
        border: 1px solid #3498db;
        background-color: #ecafaf;
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
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ _lang('Payroll Assign HR List') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payroll_assign.create') }}" id="payroll-form">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            {{-- <input type="checkbox" id="select-all"> --}}
                                        </th>
                                        <th>{{ _lang('Name') }}</th>
                                        <th>{{ _lang('Designation') }}</th>
                                        <th>{{ _lang('Net Salary') }}</th>
                                        @foreach ($salaryHeads as $salaryHead)
                                            <th>{{ __($salaryHead->name) }}
                                                ({{ $salaryHead->type == 'Addition' ? '+' : ($salaryHead->type == 'Deduction' ? '-' : '') }})
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($users as $user)
                                        @if (isset($user->userPayroll) && $user->userPayroll)
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input payroll-checkbox"
                                                            id="checkbox-{{ $user->id }}">
                                                    </div>
                                                </td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->user_type }}</td>
                                                <td>
                                                    <input
                                                        class="form-control payroll_salary_input readonly-input-net-salary"
                                                        value="{{ $user->userPayroll->net_salary ?? 0 }}" readonly>
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

                                                @foreach ($salaryHeads as $salaryHead)
                                                    @php
                                                        $payrollSalaryHead = collect(
                                                            $userSalaryHeadUserPayrolls,
                                                        )->firstWhere('salary_head_id', $salaryHead->id);
                                                    @endphp
                                                    <td>
                                                        <input class="form-control"
                                                            name="users[{{ $user->id }}][salary_heads][{{ $salaryHead->id }}]"
                                                            value="{{ $payrollSalaryHead->amount ?? 0 }}">
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">{{ _lang('Update') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle input fields based on checkbox
            document.querySelectorAll('.payroll-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    const inputFields = this.closest('tr').querySelectorAll(
                        '.payroll_salary_input');

                    inputFields.forEach(function(input) {
                        if (isChecked) {
                            input.readOnly = false;
                            input.classList.remove('readonly-input');
                            input.classList.add('editable-input');
                        } else {
                            input.readOnly = true;
                            input.classList.remove('editable-input');
                            input.classList.add('readonly-input');
                        }
                    });
                });
            });
        });
    </script>
@endsection

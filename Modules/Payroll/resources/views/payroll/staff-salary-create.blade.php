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

    .table-bordered {
        font-size: 10px !important;
    }
</style>
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        {{ _lang('Salary Config') }}
                    </h4>
                </div>
                <div class="panel-body">
                    <form method="POST" action="{{ route('staff-salary-config.store') }}" id="payroll-form">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <th>
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th>{{ _lang('Name') }}</th>
                                    <th>{{ _lang('Designation') }}</th>
                                    <th>{{ _lang('Net Salary') }}</th>
                                    @foreach ($salaryHeads as $salaryHead)
                                        <th>{{ __($salaryHead->name) }}
                                            ({{ $salaryHead->type == 'Addition' ? '+' : ($salaryHead->type == 'Deduction' ? '-' : '') }})
                                        </th>
                                    @endforeach
                                </thead>

                                <tbody>
                                    @php
                                        $count = 0;
                                    @endphp
                                    @foreach ($users as $user)
                                        @if ($user->user_type == 'Teacher' || $user->user_type == 'Staff')
                                            @php
                                                $count += 1;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="payroll-checkbox" value="">
                                                    <input type="hidden" name="staff_ids[]" class=""
                                                        value="{{ $user->id }}">
                                                </td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->user_type }}</td>
                                                <td>
                                                    <input name="net_salary"
                                                        class="payroll_salary_input readonly-input-net-salary"
                                                        value="0">
                                                </td>

                                                @foreach ($salaryHeads as $salaryHead)
                                                    <td>
                                                        <input type="number" class="payroll_salary_input readonly-input"
                                                            name="salary_heads[{{ $user->id }}][{{ $salaryHead->id }}]"
                                                            value="0" />
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="7"> <strong>Total</strong> </td>
                                        <td colspan="7"> <strong>{{ $count }}</strong> </td>
                                    </tr>
                                </tfoot>


                            </table>
                        </div>

                        <button type="submit" class="btn btn-primary"
                            style="margin-left: 1%">{{ _lang('Create') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        $(document).ready(function() {

            // Check/uncheck all checkboxes when "select-all" is clicked
            $('#select-all').click(function() {
                $('input[type=checkbox]').prop('checked', this.checked);
            });

            $('.payroll-checkbox').click(function() {
                var isChecked = $(this).prop('checked');
                var inputFields = $(this).closest('tr').find('.payroll_salary_input');
                inputFields.prop('readonly', !isChecked);
                if (isChecked) {
                    inputFields.removeClass('readonly-input').addClass('editable-input');
                } else {
                    inputFields.removeClass('editable-input').addClass('readonly-input');
                }
            });

            // Handle form submission
            $('form').submit(function() {
                // Filter out unchecked checkboxes and submit only checked rows
                var checkedRows = $('.payroll-checkbox:checked').closest('tr');

                // Process the checked rows or perform other actions
                checkedRows.each(function() {
                    // Your logic for processing each checked row
                });

                // If you want to submit only the checked rows, update the form data
                $(this).find('.payroll-checkbox:not(:checked)').closest('tr').remove();

                // Continue with form submission
                return true;
            });

        });
    </script>
@endsection

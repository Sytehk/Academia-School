@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Journal Transaction') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">
                    <h4 class="card-title">{{ _lang('Voucher Entry (Journal)') }}</h4>
                </div>

                <div class="">
                    <form action="{{ route('journal-transactions.store') }}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Journal Date') }}</label>
                                        <input type="date" class="form-control" name="transaction_date"
                                            value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Fund') }}</label>
                                        <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                            name="fund_id" required>
                                            {!! create_option('accounting_funds', 'id', 'name', old('fund_id')) !!}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row my-3 p-5">
                            <table class="table">
                                <thead>
                                    <th>Transaction For</th>
                                    <th>{{ _lang('Debit') }}</th>
                                    <th>{{ _lang('Credit') }}</th>
                                    <th>{{ _lang('Action') }}</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select class="form-control" name="cash_ledger_id" required>
                                                {!! create_option('accounting_ledgers', 'id', 'ledger_name', old('cash_ledger_id')) !!}
                                            </select>
                                            <input type="hidden" name="type" value="journal">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control cash_debit" name="cash_debit"
                                                value="">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control cash_credit" name="cash_credit"
                                                value="">
                                        </td>
                                        <td>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select class="form-control" name="ledger_ids[]" required>
                                                {!! create_option('accounting_ledgers', 'id', 'ledger_name', old('ledger_id')) !!}
                                            </select>
                                            <input type="hidden" name="type" value="journal">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control " name="debits[]" value="">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control " name="credits[]" value="">
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-success add-row" type="button">+</button>
                                            <button class="btn btn-sm btn-danger remove-row" type="button">-</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row my-3">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Description') }}</label>

                                        <textarea type="text" class="form-control" name="description" placeholder="Description" cols="2"
                                            rows="4">{{ old('description') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Ref.') }}</label>
                                        <input type="text" class="form-control" name="reference"
                                            value="{{ old('reference') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="form-group">
                                <div class="col-sm-5">
                                    <button type="submit" class="btn btn-primary mt-5">{{ _lang('Save') }}</button>
                                </div>
                            </div>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        $(document).ready(function() {
            // Function to handle debit input
            $('.cash_debit').on('input', function() {
                // Check if the input value is not empty
                if ($(this).val() !== '') {
                    // Enable credit input, disable debit input
                    $('.cash_credit').prop('readonly', true);
                    $(this).prop('readonly', false);
                } else {
                    // Both fields are cleared, enable both inputs
                    $('.cash_credit, .cash_debit').prop('readonly', false);
                }
            });

            // Function to handle credit input
            $('.cash_credit').on('input', function() {
                // Check if the input value is not empty
                if ($(this).val() !== '') {
                    // Enable debit input, disable credit input
                    $('.cash_debit').prop('readonly', true);
                    $(this).prop('readonly', false);
                } else {
                    // Both fields are cleared, enable both inputs
                    $('.cash_credit, .cash_debit').prop('readonly', false);
                }
            });
        });


        $(document).ready(function() {
            // Add row
            $(document).on("click", ".add-row", function() {
                var newRow = $(this).closest("tr").clone();
                newRow.find("select").val('');
                newRow.find("input[type='number']").val('');
                newRow.find("input[name='credits[]']").prop('readonly', false);
                newRow.find("input[name='debits[]']").prop('readonly', false);
                $(this).closest("tr").after(newRow);
            });

            // Remove row
            $(document).on("click", ".remove-row", function() {
                if ($("tbody tr").length > 1) {
                    $(this).closest("tr").remove();
                }
            });

            // Detect changes in the debits or credits input
            $(document).on("input", "input[name='debits[]'], input[name='credits[]']", function() {
                var currentRow = $(this).closest("tr");

                // If a value is entered in Debit field
                if (currentRow.find("input[name='debits[]']").val() !== '') {
                    currentRow.find("input[name='credits[]']").prop('readonly', true);
                }
                // If a value is entered in Credit field
                else if (currentRow.find("input[name='credits[]']").val() !== '') {
                    currentRow.find("input[name='debits[]']").prop('readonly', true);
                }
                // If both fields are cleared
                else {
                    currentRow.find("input[name='debits[]']").prop('readonly', false);
                    currentRow.find("input[name='credits[]']").prop('readonly', false);
                }
            });
        });
    </script>
@endsection

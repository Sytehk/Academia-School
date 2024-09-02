@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Contra Transfer') }}
@endsection
<style>
    .fund-amount {
        background-color: #f0f0f0;
        border: 1px solid #ccc;
        padding: 5px;
        border-radius: 5px;
        text-align: center;
        margin-top: 32px;
    }

    .fund-label {
        font-weight: bold;
        font-size: 16px;
        color: #333;
    }

    .fund-value {
        font-size: 20px;
        color: #008000;
        /* Green color for positive values */
    }
</style>

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">
                    <h4 class="card-title">{{ _lang('Voucher Entry (Contra)') }}</h4>
                </div>

                <form action="{{ route('account-contra-transfers.store') }}" method="post">
                    @csrf

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="my-2 control-label">{{ _lang('Transfer Date') }}</label>
                                    <input type="date" class="form-control" name="transaction_date"
                                        value="{{ date('Y-m-d') }}" required>

                                    <input type="hidden" class="form-control" name="type" value="contra">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {!! generate_select(
                                            'payment_method_id',
                                            Modules\Accounting\Models\AccountingLedger::where('accounting_category_id', 1)->pluck('ledger_name', 'id')->toArray(),
                                            _lang('Transfer From'),
                                            old('payment_method_id'),
                                            true,
                                            _lang('--Select Type--'),
                                            'form-select form-select-sm form-select-solid',
                                        ) !!}
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <div class="fund-amount">
                                            <span class="fund-label">Cash :</span>
                                            <span class="fund-value">{{ format_currency(0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="my-3 row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    {!! generate_select(
                                        'payment_method_to_id',
                                        Modules\Accounting\Models\AccountingLedger::where('accounting_category_id', 1)->pluck('ledger_name', 'id')->toArray(),
                                        _lang('Transfer To'),
                                        old('payment_method_to_id'),
                                        true,
                                        _lang('--Select Type--'),
                                        'form-select form-select-sm form-select-solid',
                                    ) !!}

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="my-2 control-label">{{ _lang('Transfer Amount') }}</label>
                                    <input type="number" class="form-control" name="amount" value="{{ old('amount') }}"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="my-2 control-label">{{ _lang('Ref.') }}</label>
                                    <input type="text" class="form-control" name="reference"
                                        value="{{ old('reference') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="my-4 row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="my-2 control-label">{{ _lang('Description') }}</label>

                                    <textarea type="text" class="form-control" name="description" placeholder="Description" cols="4"
                                        rows="4">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="mt-5 btn btn-primary">{{ _lang('Save') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
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

        $(document).ready(function() {
            $('select[name="payment_method_id"]').on('change', function() {
                var paymentMethodId = $(this).val();
                if (paymentMethodId) {
                    $.ajax({
                        url: '/ledger-account-balance', // replace with your backend route
                        type: 'GET',
                        data: {
                            payment_method_id: paymentMethodId
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data.success) {
                                $('.fund-value').text(formatCurrencyJs(data.balance));
                            } else {
                                // Handle error response
                                console.log(data.message);
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection

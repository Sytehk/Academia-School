@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Fund Transfer') }}
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
                    <h4 class="card-title">{{ _lang('Fund Transfer') }}</h4>
                </div>

                <form action="{{ route('account-fund-transfers.store') }}" method="post">
                    @csrf

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="control-label my-2">{{ _lang('Payment Date') }}</label>
                                    <input type="date" class="form-control" name="transaction_date"
                                        value="{{ date('Y-m-d') }}" required>

                                    <input type="hidden" class="form-control" name="type" value="fund_transfer">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <label class="control-label my-2">{{ _lang('Transfer From') }}</label>

                                            <select class="form-select form-select-sm form-select-solid"
                                                data-control="select2" name="fund_id" required>
                                                {!! create_option('accounting_funds', 'id', 'name', old('fund_id')) !!}
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <div class="fund-amount">
                                            <span class="fund-label">Fund :</span>
                                            <span class="fund-value">{{ format_currency(0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>

                    <div class="row my-3">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="control-label my-2">{{ _lang('Transfer To') }}</label>

                                    <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                        name="fund_to_id" required>
                                        {!! create_option('accounting_funds', 'id', 'name', old('fund_to_id')) !!}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="control-label my-2">{{ _lang('Amount') }}</label>
                                    <input type="text" class="form-control" name="amount" value="{{ old('amount') }}"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row my-4">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="control-label my-2">{{ _lang('Description') }}</label>

                                    <textarea type="text" class="form-control" name="description" placeholder="Description" cols="4"
                                        rows="4">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
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
            $('select[name="fund_id"]').on('change', function() {
                var fundMethodId = $(this).val();
                if (fundMethodId) {
                    $.ajax({
                        url: '/get-fund-balance', // replace with your backend route
                        type: 'GET',
                        data: {
                            fund_id: fundMethodId
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

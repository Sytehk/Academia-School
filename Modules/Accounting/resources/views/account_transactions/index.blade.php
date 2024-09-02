@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Transaction') }}
@endsection

@section('title', ucfirst($type) . ' ' . _lang('Transaction'))

@section('content')
    @include('accounting::account_transactions._form', [
        'type' => $type,
    ])
@endsection

@section('js-script')
    <script>
        $(document).ready(function() {
            // Add row
            $(document).on("click", ".add-row", function() {
                var newRow = $(this).closest("tr").clone();
                newRow.find("select").val('');
                newRow.find("input[type='number']").val('0');
                $(this).closest("tr").after(newRow);
            });

            // Remove row
            $(document).on("click", ".remove-row", function() {
                if ($("tbody tr").length > 1) {
                    $(this).closest("tr").remove();
                }
            });
        });
    </script>

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

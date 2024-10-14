@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('content')
    <div class="row justify-content-between">
        <div class="col-lg-5">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('New Purchase') }}
                    </div>
                </div>

                <div class="">
                    <form action="{{ route('sms-purchase.store') }}"enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang('Quantity') }}</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" name="no_of_sms" value="{{ old('no_of_sms') }}"
                                    placeholder="Quantity" required id='no_of_sms'>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang('Price') }}</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" name="amount" id='amount'
                                    value="{{ old('amount') }}" placeholder="Amount" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang('Transaction date') }}</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" name="transaction_date"
                                    value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                <!-- `date('Y-m-d')` will provide the current date in the 'Y-m-d' format -->
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang('Masking Type') }}</label>
                            <div class="col-sm-9">
                                <select name="masking_type" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" id="" required>
                                    <option value="nonmasking">Non Masking/General</option>
                                    <option value="masking">Masking</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang('SMS Gateway') }}</label>
                            <div class="col-sm-9">
                                <select name="sms_gateway" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" id="" required>
                                    <option value="" readonly>{{ _lang('--Select--') }}</option>
                                    <option value="bulksmsbd">Bulk SMS BD</option>
                                    <option value="mimsms">MimSMS</option>
                                    <option value="twilio">Twilio</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">
                                    {{ _lang('Create') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Purchases') }}
                    </div>
                </div>

                <div class="">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('SMS Gateway') }}</th>
                            <th>{{ _lang('Transaction Date') }}</th>
                            <th>{{ _lang('Price') }}</th>
                            <th>{{ _lang('Quantity') }}</th>
                            <th>{{ _lang('Maskinhg/Non Masking') }}</th>
                        </thead>
                        <tbody>
                            @if (isset($smsPurchases))
                                @foreach ($smsPurchases as $data)
                                    <tr>

                                        <td>{{ $data->sms_gateway }}</td>
                                        <td>{{ $data->transaction_date }}</td>
                                        <td>{{ $data->amount }}</td>
                                        <td>
                                            {{ $data->no_of_sms }}
                                        </td>
                                        <td>
                                            {{ $data->masking_type }}
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-danger">No Student Found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @endsection
    @section('js-script')
        <script>
            $('#no_of_sms').on('input', function() {
                var noOfSms = $(this).val();
                var maskingType = $('select[name="masking_type"]').val(); // Get the selected masking type

                var rate = (maskingType === 'masking') ? 0.50 : 0.25; // Set the rate based on masking type

                var calculatedAmount = rate * noOfSms;
                $('#amount').val(calculatedAmount.toFixed(2));
            });

            // Add an event listener for the masking type dropdown change
            $('select[name="masking_type"]').on('change', function() {
                var noOfSms = $('#no_of_sms').val();
                var maskingType = $(this).val();

                var rate = (maskingType === 'masking') ? 0.50 : 0.25;

                var calculatedAmount = rate * noOfSms;
                $('#amount').val(calculatedAmount.toFixed(2));
            });
        </script>
    @stop

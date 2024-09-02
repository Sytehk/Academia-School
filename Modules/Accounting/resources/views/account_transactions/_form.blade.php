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

<div class="row">
    <div class="col-lg-12">
        <div class="">
            <div class="card-header">
                <span class="text-center card-title">
                    {{ get_accounting_types($type) }}
                </span>
            </div>

            <form action="{{ route('account-transaction-payment.store') }}" method="post">
                @csrf

                {{-- Hidden transaction type --}}
                <input type="text" name="type" id="type" value="{{ $type }}" hidden />

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">
                                    @if ($type === 'payment')
                                        {{ _lang('Payment Date') }}
                                    @endif
                                    @if ($type === 'receipt')
                                        {{ _lang('Receipt Date') }}
                                    @endif
                                </label>
                                <input type="date" class="form-control" name="transaction_date"
                                    value="{{ date('Y-m-d') }}" required>
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
                                        $type === 'payment' ? _lang('Payment By') : _lang('Receipt Type'),
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

                <div class="row my-3 px-3">
                    <table class="table">
                        <thead>
                            <th>{{ _lang('Transaction For') }}</th>
                            <th>{{ _lang('Amount') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    @php
                                        if ($type === 'payment') {
                                            $excluded_category_ids = [1, 2, 3, 4, 5, 6];
                                        }

                                        if ($type === 'receipt') {
                                            $excluded_category_ids = [1, 7];
                                        }

                                        $accounting_ledgers = Modules\Accounting\Models\AccountingLedger::whereNotIn(
                                            'accounting_category_id',
                                            $excluded_category_ids,
                                        )->get();
                                    @endphp

                                    <select class="form-control" name="ledger_ids[]">
                                        <option>--Select Ledger--</option>
                                        @foreach ($accounting_ledgers as $ledger)
                                            <option value="{{ $ledger->id }}">{{ $ledger->ledger_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control" name="amounts[]" value="0">
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
                                <label class="control-label my-2">{{ _lang('Fund') }}</label>
                                <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                    name="fund_id">
                                    {!! create_option('accounting_funds', 'id', 'name', old('fund_id')) !!}
                                </select>
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

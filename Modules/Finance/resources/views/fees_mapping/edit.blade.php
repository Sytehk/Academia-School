@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Fee Mapping') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Edit') }} {{ $feeMap->type === 'fee_fine' ? _lang('Fee Fine Mapping') : _lang('Fee Mapping') }}
                    </div>
                </div>

                <div class="card-body">
                    <form method="post" action="{{ route('fees-mapping.update', $feeMap->id) }}">
                        @csrf
                        @method('PATCH')

                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Fee Head') }}</label>
                            <select name="fee_head_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" required>
                                <option value="">{{ _lang('Select Head') }}</option>
                                @foreach (\Modules\Finance\Models\FeeHead::orderBy('serial', 'asc')->get() as $feeHead)
                                    <option value="{{ $feeHead->id }}"
                                        {{ old('fee_head_id', $feeMap->fee_head_id) == $feeHead->id ? 'selected' : '' }}>
                                        {{ $feeHead->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($feeMap->type === 'fee')
                            <div class="form-group mt-4">
                                <label class="control-label my-2">{{ _lang('Fee Sub Heads') }}</label>
                                <select name="fee_sub_head_ids[]" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" multiple required>
                                    @php
                                        $selectedSubHeads = old(
                                            'fee_sub_head_ids',
                                            $feeMap->feeSubHeads->pluck('id')->toArray(),
                                        );
                                    @endphp
                                    @foreach (\Modules\Finance\Models\FeeSubHead::orderBy('serial', 'asc')->get() as $feeSubHead)
                                        <option value="{{ $feeSubHead->id }}"
                                            {{ in_array($feeSubHead->id, $selectedSubHeads) ? 'selected' : '' }}>
                                            {{ $feeSubHead->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="form-group mt-4">
                            <label class="control-label my-2">{{ _lang('Ledger') }}</label>
                            <select name="ledger_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" required>
                                <option value="">{{ _lang('Select Ledger') }}</option>
                                @foreach (\Modules\Accounting\Models\AccountingLedger::all() as $ledger)
                                    <option value="{{ $ledger->id }}"
                                        {{ old('ledger_id', $feeMap->ledger_id) == $ledger->id ? 'selected' : '' }}>
                                        {{ $ledger->ledger_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($feeMap->type === 'fee')
                            <div class="form-group mt-4">
                                <label class="control-label my-2">{{ _lang('Fund') }}</label>
                                <select name="fund_ids[]" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" multiple>
                                    @php
                                        $selectedFunds = old('fund_ids', $feeMap->funds->pluck('id')->toArray());
                                    @endphp
                                    @foreach (\Modules\Accounting\Models\AccountingFund::all() as $fund)
                                        <option value="{{ $fund->id }}"
                                            {{ in_array($fund->id, $selectedFunds) ? 'selected' : '' }}>
                                            {{ $fund->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="form-group mt-4">
                                <label class="control-label my-2">{{ _lang('Fund') }}</label>
                                <select name="fund_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" required>
                                    <option value="">{{ _lang('Select Fund') }}</option>
                                    @php
                                        $selectedFundId = old('fund_id', $feeMap->funds->first()->id ?? '');
                                    @endphp
                                    @foreach (\Modules\Accounting\Models\AccountingFund::all() as $fund)
                                        <option value="{{ $fund->id }}"
                                            {{ (string) $selectedFundId === (string) $fund->id ? 'selected' : '' }}>
                                            {{ $fund->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="form-group mt-5">
                            <button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
                            <a href="{{ route('fees-mapping.index') }}" class="btn btn-secondary">{{ _lang('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

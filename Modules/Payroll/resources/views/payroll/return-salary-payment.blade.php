@extends('layouts.backend')
<style>
    .payroll_salary_input {
        width: 100px !important;
        border: none !important;
    }
</style>
@section('content')
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <div class="panel panel-default" data-collapsed="0">
                <div class="panel-heading">
                    <span class="panel-title">
                        {{ _lang('Return Payment') }}
                    </span>
                </div>
                <div class="panel-body">

                    <form action="{{ route('return.salary.payment.check') }}" class="form-horizontal form-groups-bordered"
                        method="post">
                        @csrf

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label">{{ _lang('HR ID') }}</label>
                                <input type="text" class="form-control" name="user_id" value="{{ $user->id ?? '' }}"
                                    placeholder="HR ID">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-info">
                                    <i class="fa fa-search"></i>
                                    {{ _lang('Search') }}
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    @if (isset($user))
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default" data-collapsed="0">
                    <div class="panel-heading">
                        <span class="panel-title">
                            {{ _lang('HR Details') }}
                        </span>
                    </div>
                    <div class="panel-body">
                        <form action="{{ route('return.salary.pay') }}" class="form-horizontal form-groups-bordered"
                            method="post">
                            @csrf

                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <label class="control-label">{{ _lang('Name') }}</label>
                                                <input type="text" class="form-control" value="{{ $user->name }}"
                                                    readonly>
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <input type="hidden" name="type" value="advanced_return">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <label class="control-label">{{ _lang('Payment Date') }}</label>
                                                <input type="date" class="form-control" name="date"
                                                    value="{{ old('date') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <label class="control-label">{{ _lang('Paid') }}</label>
                                                <input type="number" class="form-control" name="amount"
                                                    value="{{ old('amount') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <label class="control-label">{{ _lang('Paid By') }}</label>

                                                <select class="form-control select2" name="payment_method_id">
                                                    @foreach ($cashLedgers as $cashLedger)
                                                        <option value="{{ $cashLedger->id }}"
                                                            {{ old('payment_method_id') == $cashLedger->id ? 'selected' : '' }}>
                                                            {{ $cashLedger->ledger_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <label class="control-label">{{ _lang('Designation') }}</label>
                                                <input type="text" class="form-control" readonly
                                                    value="{{ old('designation') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <label class="control-label">{{ _lang('Total Payable') }}</label>
                                                <input type="number" class="form-control" name="total_payable"
                                                    value="{{ $userPayroll->net_salary }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <label class="control-label">{{ _lang('Advance') }}</label>
                                                <input type="number" class="form-control" readonly
                                                    value="{{ $userPayroll->current_advance ?? 0 }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <label class="control-label">{{ _lang('Note') }}</label>
                                                <textarea class="form-control" name="note" rows="4" cols="3">{{ old('note') }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="col-sm-5">
                                                <button type="submit" class="btn btn-info">{{ _lang('Save') }}</button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

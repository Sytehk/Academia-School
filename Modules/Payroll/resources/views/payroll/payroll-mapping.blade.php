@extends('layouts.backend')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="panel panel-default" data-collapsed="0">
                <div class="panel-heading">
                    <span class="panel-title">
                        {{ _lang('Payroll Mapping') }}
                    </span>
                </div>
                <div class="panel-body">
                    <form action="{{ route('payroll_mapping.store') }}" method="POST" autocomplete="off"
                        class="form-horizontal form-groups-bordered validate" method="post" accept-charset="utf-8">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label">{{ _lang('Paid By') }}</label>

                                        <select class="form-control select2" name="ledger_id">
                                            {!! create_option('accounting_ledgers', 'id', 'ledger_name', old('ledger_id')) !!}
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label">{{ _lang('Paid By') }}</label>
                                        <select class="form-control select2" name="fund_id">
                                            {!! create_option('accounting_funds', 'id', 'name', old('fund_id')) !!}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-info">{{ _lang('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>


                @if (isset($data))
                    <div class="panel-body">
                        <table class="table table-bordered data-table">
                            <thead>
                                <th>{{ _lang('Ledger') }}</th>
                                <th>{{ _lang('Fund') }}</th>
                            </thead>
                            <tbody>
                                @foreach ($data as $single)
                                    <tr>
                                        <td>
                                            @php
                                                $ledger = Modules\Accounting\Models\AccountingLedger::where(
                                                    'id',
                                                    $single->ledger_id,
                                                )->first();

                                            @endphp
                                            {{ $ledger->ledger_name }}
                                        </td>
                                        <td>
                                            @php
                                                $fund = Modules\Accounting\Models\AccountingFund::where(
                                                    'id',
                                                    $single->fund_id,
                                                )->first();
                                            @endphp
                                            {{ $fund->name }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>

    </div>
@endsection

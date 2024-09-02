@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Accounting Fund') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Add New Fund') }}
                    </span>
                </div>
                <div class="">
                    <form action="{{ route('accounting-funds.store') }}"method="post">
                        @csrf

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Accouting Fund Name') }}</label>
                                <input type="text" class="form-control" name="name" required
                                    value="{{ old('name') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Serial') }}</label>
                                <input type="number" class="form-control" name="serial" min="1"
                                    value="{{ old('serial') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Accounting Fund List') }}
                    </span>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Serial') }}</th>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Amount In') }}</th>
                            <th>{{ _lang('Amount Out') }}</th>
                            <th>{{ _lang('Balance') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($accountingFunds as $accountingFund)
                                <tr>
                                    <td>{{ $accountingFund->serial }}</td>
                                    <td>{{ $accountingFund->name }}</td>
                                    @php
                                        $debitTotal = 0;
                                        $creditTotal = 0;
                                    @endphp

                                    @foreach ($accountingFund->accountingFundsTransactions as $transaction)
                                        @php
                                            $debitTotal += floatval($transaction['debit']);
                                            $creditTotal += floatval($transaction['credit']);
                                        @endphp
                                    @endforeach

                                    <td>
                                        {{ format_currency($creditTotal) }}
                                    </td>

                                    <td>
                                        {{ format_currency($debitTotal) }}
                                    </td>

                                    <td>
                                        {{ format_currency($accountingFund->balance) }}
                                    </td>

                                    <td>
                                        <form action="{{ route('accounting-funds.destroy', $accountingFund->id) }}"
                                            method="post">
                                            <a href="{{ route('accounting-funds.edit', $accountingFund->id) }}"
                                                class="btn btn-warning btn-sm"><i class="fa fa-pencil"
                                                    aria-hidden="true"></i></a>
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm btn-remove"><i
                                                    class="fa fa-trash" aria-hidden="true"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

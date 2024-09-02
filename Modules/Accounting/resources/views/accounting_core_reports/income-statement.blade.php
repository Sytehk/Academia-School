@extends('layouts.backend')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default border">
                <div class="panel-heading">
                    <span class="panel-title">{{ _lang('Income Statement') }}</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('income-statement') }}" method="get">

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label ">{{ _lang('From') }}</label>
                                        <input type="date" class="form-control" name="from_date"
                                            value="{{ $startDate ?? Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label ">{{ _lang('To') }}</label>
                                        <input type="date" class="form-control" name="to_date"
                                            value="{{ $endDate ?? date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>


                            {{-- <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label datepicker">{{ _lang('Voucher Type') }}</label>
                                        <select class="form-control" name="type" id="">
                                            <option value="">--Select Type--</option>
                                            <option {{ $type == 'All' ? 'selected' : '' }} value="All">
                                                All
                                            </option>
                                            <option {{ $type == 'Receipt' ? 'selected' : '' }} value="Receipt">Receipt
                                            </option>
                                            <option {{ $type == 'Payment' ? 'selected' : '' }} value="Payment">Payment
                                            </option>

                                            <option {{ $type == 'Journal' ? 'selected' : '' }} value="Journal">Journal
                                            </option>
                                            <option {{ $type == 'Contra' ? 'selected' : '' }} value="Contra">Contra
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="col-sm-5">
                                        <button type="submit" class="btn btn-info mt-3">{{ _lang('Search') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if (isset($ledgers) && !empty($ledgers) && count($ledgers) > 0)
                <div class="card-border">
                    <div class="card-header py-3 d-flex justify-content-between">
                        <div>
                            <p> Voucher List from <strong>{{ $fromDate }}</strong> to
                                <strong>{{ $toDate }}</strong>
                            </p>
                        </div>

                        <div>
                            <p class="">Total Found: <strong>{{ count($ledgers) }}</strong></p>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered data-table">
                            <thead>
                                <th colspan="4">{{ _lang('Account Ledgers') }}</th>
                                <th>{{ _lang('Debit Amount') }}</th>
                                <th>{{ _lang('Credit Amount') }}</th>
                            </thead>
                            <tbody>
                                @php
                                    $totalDebit = 0;
                                    $totalCredit = 0;
                                @endphp
                                @foreach ($ledgers as $ledger)
                                    <tr>
                                        <td colspan="4" class="ledger-details">
                                            <a
                                                href="{{ route('trial-balance-details', ['ledger_id' => $ledger->id]) }}">{{ $ledger->ledger_name }}</a>
                                        </td>

                                        <td class="text-right">
                                            @if ($ledger->nature == 'debit')
                                                {{ format_currency($ledger->balance) }}
                                            @else
                                                0
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if ($ledger->nature == 'credit')
                                                {{ format_currency($ledger->balance) }}
                                            @else
                                                0
                                            @endif
                                        </td>

                                        @php
                                            $totalDebit += $ledger->nature == 'debit' ? $ledger->balance : 0;
                                            $totalCredit += $ledger->nature == 'credit' ? $ledger->balance : 0;
                                        @endphp
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr class="bg-primary">
                                    <td colspan="4" class="text-right"> <strong
                                            class="text-white">{{ _lang('Total') }}</strong> </td>
                                    <td class="text-right"> <strong class="text-white">{{ format_currency($totalDebit) }}
                                        </strong> </td>
                                    <td class="text-right"> <strong class="text-white">{{ format_currency($totalCredit) }}
                                        </strong> </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection

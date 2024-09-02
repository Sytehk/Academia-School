@extends('layouts.backend')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default ">
                <div class="panel-heading">
                    <span class="panel-title">{{ _lang('Balance Sheet') }}</s>
                </div>
                <div class="card-body">
                    <form action="{{ route('balance-sheet') }}" method="get">
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

            {{-- @if (isset($ledgers) && !empty($ledgers) && count($ledgers) > 0)
                <div class="card-border">
                    <div class="card-header py-3 d-flex justify-content-between">
                        <div>
                            <p> Account Ledgers From <strong> {{ $startDate }} to {{ $endDate }}</strong>
                            </p>
                        </div>

                        <div>
                            <p class="">Total Found: <strong>{{ count($ledgers) ?? 0 }}</strong></p>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered data-table">
                            <thead>
                                <th colspan="4">{{ _lang('Account Ledger') }}</th>
                                <th>{{ _lang('Debit') }}</th>
                                <th>{{ _lang('Credit') }}</th>
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
                                                href="{{ route('balance-sheet-details', ['ledger_id' => $ledger->id]) }}">{{ $ledger->ledger_name }}</a>
                                        </td>

                                        <td class="text-right">
                                            @if ($ledger->nature == 'debit')
                                                {{ $ledger->balance }}
                                            @else
                                                0
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if ($ledger->nature == 'credit')
                                                {{ $ledger->balance }}
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
                                        </strong>
                                    </td>
                                    <td class="text-right"> <strong class="text-white">{{ format_currency($totalCredit) }}
                                        </strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif --}}

        </div>
    </div>
@endsection

@extends('layouts.pdf.index')
@section('styles')
    <link href="{{ asset('backend') }}/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: rgb(214, 212, 212)
        }

        header {
            height: 153px !important;
        }

        .form-view {
            width: 1122px !important;
        }
    </style>
@endsection
@section('content-download')
    <div class="noprint print-download-buttons">
        @include('layouts.pdf.back-button')
        {{-- @include('layouts.pdf.download-button') --}}
        @include('layouts.pdf.print-button')

    </div>
    <div style="clear: both;"></div>
@endsection
@section('content')
    @if (isset($transactions) && !empty($transactions) && count($transactions) > 0)
        @include('layouts.pdf.header')

        <div class="card-border">
            <div class="card-header py-3 d-flex justify-content-between">
                <div>
                    <p>Fund Wise Summary Between <strong>{{ $fromDate }}</strong> to
                        <strong>{{ $toDate }}</strong>
                    </p>
                </div>

                {{-- <div>
                    <p class="">Total Found -
                        <strong>
                            {{ count($transactions) }}
                        </strong>
                    </p>
                </div> --}}
            </div>
            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <th>{{ _lang('Trn Date') }}</th>
                        <th>{{ _lang('Voucher ID') }}</th>
                        <th>{{ _lang('Fund Name') }}</th>
                        <th>{{ _lang('Amount In') }}</th>
                        <th>{{ _lang('Amount Out') }}</th>
                    </thead>

                    <tbody>
                        @php
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->transaction_date }}</td>
                                <td>{{ $transaction->voucher_id }}</td>
                                <td>{{ $transaction->fund_name }}</td>
                                <td>{{ format_currency($transaction->credit) }}</td>
                                <td>{{ format_currency($transaction->debit) }}</td>
                            </tr>

                            @php
                                $totalDebit += $transaction->debit;
                                $totalCredit += $transaction->credit;
                            @endphp
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="bg-primary">
                            <td colspan="3" class="text-right"> <strong class="text-white">{{ _lang('Total') }}</strong>
                            </td>
                            <td> <strong class="text-white">{{ format_currency($totalCredit) }} </strong> </td>
                            <td> <strong class="text-white">{{ format_currency($totalDebit) }} </strong> </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
@endsection

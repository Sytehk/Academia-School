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
    {{-- <table class="table table-bordered data-table">
        <tbody>
            @foreach ($transactions as $transaction)
                <tr>
                    <td colspan="6">
                        Voucher ID {{ $transaction->voucher_id }}, Type {{ $transaction->type }} (Posted by
                        {{ $transaction->created_by }})
                    </td>
                    <td colspan="2">{{ $transaction->transaction_date }}</td>
                </tr>
                @foreach ($transaction->transactionDetails as $item)
                    <td>{{ $item->accountingLedger ? $item->accountingLedger->ledger_name : '' }}</td>
                    <td>{{ $item->debit }}</td>
                    <td>{{ $item->credit }}</td>
                @endforeach
                </tr>
            @endforeach
        </tbody>
    </table> --}}

    @include('layouts.pdf.header')

    <div class="card-border">
        <div class="card-header py-3 d-flex justify-content-between">
            <div>
                <p>Account Journal
                </p>
            </div>
        </div>
        <div class="card-body">
            @foreach ($transactions as $transaction)
                <table class="table table-bordered data-table">
                    <thead>
                        <th>
                            Voucher ID {{ $transaction->voucher_id }}, Type {{ $transaction->type }} (Posted by
                            {{ $transaction->user->name }})
                        </th>
                        <th colspan="2" style="text-align: center">
                            {{ $transaction->transaction_date }}
                        </th>
                    </thead>
                    <tbody>
                        @php
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp
                        <tr>
                            <th>Ledger</th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                        @foreach ($transaction->transactionDetails as $item)
                            <tr>
                                <td>{{ $item->accountingLedger ? $item->accountingLedger->ledger_name : '' }}</td>
                                <td>{{ $item->debit }}</td>
                                <td>{{ $item->credit }}</td>
                            </tr>
                            @php
                                $totalDebit += $item->debit;
                                $totalCredit += $item->credit;
                            @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="1"></td>
                            <td colspan="1">
                                <strong>Total Debit: </strong> {{ $totalDebit }}
                            </td>
                            <td colspan="1"> <strong>Total Credit: </strong> {{ $totalCredit }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endforeach
        @endsection

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

@section('title')
    {{ _lang('Voucher Wise Transaction') }}
@endsection

@section('content')
    @include('layouts.pdf.header')
    <div class="card-border">
        <div class="card-header py-3 justify-content-between">
            <h2 class="text-center text-underline">Journal Voucher</h2>
            <div>
                <ul style="list-style: none; padding-left: 5px;">
                    <li tyle="list-style: none;">
                        <span> Voucher ID</span> : <span>{{ $transactions->voucher_id }}</span>
                    </li>

                    <li tyle="list-style: none;">
                        <span> Voucher No</span> : <span>null</span>
                    </li>

                    <li tyle="list-style: none;">
                        <span> Ref. No. </span> : <span>{{ $transactions->reference }}</span>
                    </li>

                    <li tyle="list-style: none;">
                        <span> Entry By </span> : <span>{{ $transactions->user?->phone }}</span>
                    </li>

                    <li tyle="list-style: none;">
                        <span> Description </span> : <span>{{ $transactions->description }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>{{ _lang('Ledger') }}</th>
                        <th>{{ _lang('Debit Amount') }}</th>
                        <th>{{ _lang('Credit Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $debitTotal = 0;
                        $creditTotal = 0;
                    @endphp
                    @foreach ($transactions->transactionDetails as $transaction)
                        <tr>
                            <td>{{ $transaction->accountingLedger?->ledger_name }}</td>
                            <td>{{ format_currency($transaction->debit) }}</td>
                            <td>{{ format_currency($transaction->credit) }}</td>
                        </tr>
                        @php
                            $debitTotal += $transaction->debit;
                            $creditTotal += $transaction->credit;
                        @endphp
                    @endforeach


                    <tr>
                        <td style="text-align: right"><strong class="text-white">{{ _lang('Total') }}</strong></td>
                        <td><strong class="text-white">{{ format_currency($debitTotal) }}</strong></td>
                        <td><strong class="text-white">{{ format_currency($creditTotal) }}</strong></td>
                    </tr>

                    <tr>
                        <td colspan="3">
                            <strong class="text-white">In Word : </strong>
                            {{ convert_amount_to_words($debitTotal) }}

                        </td>

                    </tr>
                </tbody>
            </table>

            <table style="margin-top: 100px;">
                <tr style="text-align: center">
                    <td> <span
                            style="border-top:2px dashed black; font-size: 14px; font-weight: 600; padding: 0 15px;">Approved
                            By</span>
                    </td>
                    <td> <span
                            style="border-top:2px dashed black; font-size: 14px; font-weight: 600; padding: 0 15px;">Received
                            By</span>
                    </td>
                    <td> <span
                            style="border-top:2px dashed black; font-size: 14px; font-weight: 600; padding: 0 15px;">Accountant</span>
                    </td>
                </tr>
            </table>

            <table style="margin: 60px 0;">
                <tr style="text-align: center">
                    <td> <span
                            style="border-top:2px dashed black; font-size: 14px; font-weight: 600; padding: 0 15px;">Date</span>
                    </td>
                    <td> <span
                            style="border-top:2px dashed black; font-size: 14px; font-weight: 600; padding: 0 15px;">Date</span>
                    </td>
                    <td> <span
                            style="border-top:2px dashed black; font-size: 14px; font-weight: 600; padding: 0 15px;">Date</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endsection

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
            <div class="card-header py-3 justify-content-between">
                <div>
                    <p class="pdf-page-title"> User Wise Transaction Between <strong>{{ $fromDate }}</strong> to
                        <strong>{{ $toDate }}</strong>
                    </p>
                </div>

                <div>
                    <p class="">Total Found - <strong>{{ count($transactions) }}</strong></p>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <th>{{ _lang('User') }}</th>
                        <th>{{ _lang('Ledger') }}</th>
                        <th>{{ _lang('Debit Amount') }}</th>
                        <th>{{ _lang('Credit Amount') }}</th>
                    </thead>

                    <tbody>
                        @php
                            $first = true;
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp
                        @foreach ($transactions as $transaction)
                            <tr>
                                @if ($first)
                                    <td rowspan="{{ count($transactions) }}">{{ auth()->user()->id }}</td>
                                    @php $first = false; @endphp
                                @endif
                                <td>{{ $transaction->ledger_name }}</td>
                                <td>{{ format_currency($transaction->debit) }}</td>
                                <td>{{ format_currency($transaction->credit) }}</td>
                                @php
                                    $totalDebit += $transaction->debit;
                                    $totalCredit += $transaction->credit;
                                @endphp
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="bg-primary">
                            <td colspan="2" class="text-right"> <strong class="text-white">{{ _lang('Total') }}</strong>
                            </td>
                            <td> <strong class="text-white">{{ format_currency($totalDebit) }} </strong> </td>
                            <td> <strong class="text-white">{{ format_currency($totalCredit) }} </strong> </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
@endsection

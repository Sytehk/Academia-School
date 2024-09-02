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

        @php
            $ledgerData = [];
            $ledgerCount = 0;
        @endphp

        @foreach ($transactions as $transaction)
            @php
                $ledgerName = $transaction->ledger_name;
                $debit = $transaction->debit;
                $credit = $transaction->credit;
            @endphp

            @if (!isset($ledgerData[$ledgerName]))
                @php
                    $ledgerData[$ledgerName] = ['debit' => 0, 'credit' => 0];
                    $ledgerCount++;
                @endphp
            @endif

            @php
                $ledgerData[$ledgerName]['debit'] += $debit;
                $ledgerData[$ledgerName]['credit'] += $credit;
            @endphp
        @endforeach

        <div class="card-border">
            <div class="card-header py-3 d-flex justify-content-between">
                <div>
                    <p>Ledger Summary Between <strong>{{ $fromDate }}</strong> to
                        <strong>{{ $toDate }}</strong>
                    </p>
                </div>

                {{-- <div>
                    <p class="">Total Found -
                        <strong>{{ $ledgerCount }}</strong>
                    </p>
                </div> --}}
            </div>
            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <th>{{ _lang('Ledger') }}</th>
                        <th>{{ _lang('Debit Amount') }}</th>
                        <th>{{ _lang('Credit Amount') }}</th>
                    </thead>

                    <tbody>
                        @foreach ($ledgerData as $ledgerName => $data)
                            <tr>
                                <td>{{ $ledgerName }}</td>
                                <td>{{ format_currency($data['debit']) }}</td>
                                <td>{{ format_currency($data['credit']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        @php
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp

                        @foreach ($ledgerData as $data)
                            @php
                                $totalDebit += $data['debit'];
                                $totalCredit += $data['credit'];
                            @endphp
                        @endforeach

                        <tr class="bg-primary">
                            <td colspan="1" class="text-right"> <strong class="text-white">{{ _lang('Total') }}</strong>
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

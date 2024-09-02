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
                    <p>Monthly Fund Transaction
                    </p>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <th>{{ _lang('Monthly') }}</th>
                        <th>{{ _lang('Flow In') }}</th>
                        <th>{{ _lang('Flow Out') }}</th>
                        <th>{{ _lang('Net Flow') }}</th>
                    </thead>

                    <tbody>
                        @php
                            $monthData = [];
                            $currentMonth = null;
                            $totalDebit = 0;
                            $totalCredit = 0;

                            foreach ($transactions as $transaction) {
                                $date = \Carbon\Carbon::parse($transaction->transaction_date);
                                $month = $date->format('F');

                                if ($month !== $currentMonth) {
                                    // Calculate the remaining balance for the previous month and store it
                                    if ($currentMonth !== null) {
                                        $monthData[$currentMonth]['debit'] = $totalDebit;
                                        $monthData[$currentMonth]['credit'] = $totalCredit;
                                    }

                                    // Initialize the current month
                                    $currentMonth = $month;
                                    $monthData[$currentMonth] = [
                                        'debit' => 0,
                                        'credit' => 0,
                                    ];
                                }

                                // Update the debit and credit for the current month
                                $monthData[$currentMonth]['debit'] += $transaction->debit;
                                $monthData[$currentMonth]['credit'] += $transaction->credit;

                                // Update the total debit and credit for the overall calculation
                                $totalDebit += $transaction->debit;
                                $totalCredit += $transaction->credit;
                            }
                        @endphp

                        @foreach ($monthData as $month => $data)
                            <tr>
                                <td>{{ $month }}</td>
                                <td>{{ format_currency($data['credit']) }}</td>
                                <td>{{ format_currency($data['debit']) }}</td>
                                <td>{{ format_currency($data['credit'] - $data['debit']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot class="bg-primary">
                        @php
                            $totalDebit = 0;
                            $totalCredit = 0;

                            foreach ($monthData as $data) {
                                $totalDebit += $data['debit'];
                                $totalCredit += $data['credit'];
                            }
                        @endphp
                        <tr>
                            <td colspan="1" class="text-right"> <strong class="text-white">{{ _lang('Total') }}</strong>
                            </td>
                            <td>{{ format_currency($totalCredit) }}</td>
                            <td>{{ format_currency($totalDebit) }}</td>
                            <td>{{ format_currency($totalCredit - $totalDebit) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
@endsection

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
    @if (isset($ledgers) && !empty($ledgers) && count($ledgers) > 0)
        @include('layouts.pdf.header')

        <div class="card-border">
            <div class="card-header py-3 d-flex justify-content-between">
                <div>
                    <p>Balance Sheet from <strong>{{ $startDate }}</strong> to
                        <strong>{{ $endDate }}</strong>
                    </p>
                </div>

                <div>
                    <p class="" style="margin-right: 50px;">Total Found: <strong>{{ count($ledgers) ?? 0 }}</strong></p>
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
                                    <a href="#">{{ $ledger->ledger_name }}</a>
                                </td>

                                <td class="text-right">
                                    {{ $ledger->total_debit }}
                                </td>
                                <td class="text-right">
                                    {{ $ledger->total_credit }}
                                </td>
                                @php
                                    $totalDebit += $ledger->total_debit ? $ledger->total_debit : 0;
                                    $totalCredit += $ledger->total_credit ? $ledger->total_credit : 0;
                                @endphp
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="bg-primary">
                            <td colspan="4" class="text-right"> <strong class="text-white">{{ _lang('Total') }}</strong>
                            </td>
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
    @endif

@endsection

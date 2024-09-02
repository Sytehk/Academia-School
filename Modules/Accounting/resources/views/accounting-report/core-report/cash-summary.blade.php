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
                    <p> Cash Summary <strong>{{ $fromDate }}</strong>
                    </p>
                </div>

                {{-- <div>
                    <p class="">Total Found: <strong>{{ count($ledgers) ?? 0 }}</strong></p>
                </div> --}}
            </div>
            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <th colspan="4">{{ _lang('Ledger-Income List') }}</th>
                        {{-- <th>{{ _lang('Debit') }}</th> --}}
                        <th colspan="4" class="text-right">{{ _lang('Income List') }}</th>
                    </thead>
                    <tbody>
                        @php
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp
                        @foreach ($income as $ledger)
                            <tr>
                                <td colspan="4" class="ledger-details">
                                    <a
                                        href="{{ route('balance-sheet-details', ['ledger_id' => $ledger->id]) }}">{{ $ledger->ledger_name }}</a>
                                </td>

                                {{-- <td class="text-right">

                                    {{ $ledger->total_debit }}

                                </td> --}}
                                <td colspan="4" class="text-right">

                                    {{ $ledger->total_credit }}

                                </td>

                                @php
                                    // $totalDebit += $ledger->total_debit;
                                    $totalCredit += $ledger->total_credit;
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
                {{-- ex --}}
                <table class="table table-bordered data-table">
                    <thead>
                        <th colspan="4">{{ _lang('Ledger-Expense List') }}</th>
                        <th colspan="4" class="text-right">{{ _lang('Expense List') }}</th>
                        {{-- <th>{{ _lang('Credit') }}</th> --}}
                    </thead>
                    <tbody>

                        @foreach ($expense as $ledger)
                            <tr>
                                <td colspan="4" class="ledger-details">
                                    <a
                                        href="{{ route('balance-sheet-details', ['ledger_id' => $ledger->id]) }}">{{ $ledger->ledger_name }}</a>
                                </td>

                                <td colspan="4" class="text-right">

                                    {{ $ledger->total_debit }}

                                </td>
                                {{-- <td class="text-right">

                                    {{ $ledger->total_credit }}

                                </td> --}}

                                @php
                                    $totalDebit += $ledger->total_debit;
                                    // $totalCredit += $ledger->total_credit ;
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
                            <td class="text-right"> <strong class="text-white">{{ format_currency(0) }}
                                </strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                {{-- profilte/ los --}}
                {{-- <table class="table table-bordered data-table">
                    <thead>
                        <th colspan="4">{{ _lang('Ledger-Profite/Loss') }}</th>

                        


                    </thead>

                    <tbody>
                        @php
                            $totalDebCred = $totalCredit - $totalDebit;
                        @endphp
                        <tr>
                            <td>Excess Income Over Expenditure {{ $totalDebCred }}
                            </td>
                            <td>{{ $totalDebCred < 0 ? $totalDebCred : 0 }}</td>
                            <td>{{ $totalDebCred < 0 ? 0 : -$totalDebCred }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-primary">
                            <td colspan="" class="text-right"> <strong class="text-white">{{ _lang('Total') }}</strong>
                            </td>
                            <td class="text-right"> <strong
                                    class="text-white">{{ format_currency($totalDebit + $totalDebCred) }}
                                </strong>
                            </td>
                            <td class="text-right"> <strong class="text-white">{{ format_currency($totalCredit) }}
                                </strong>
                            </td>
                        </tr>
                    </tfoot>
                </table> --}}

            </div>
        </div>
    @endif

@endsection

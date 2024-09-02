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
    @if (isset($accountTransactionDetails) && !empty($accountTransactionDetails) && count($accountTransactionDetails) > 0)
        <div class="card-border">
            <div class="card-header py-3 d-flex justify-content-between">

                <div>
                    <p class="">Total Found: <strong>{{ count($accountTransactionDetails) ?? 0 }}</strong></p>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <th colspan="4">{{ _lang('Month') }}</th>
                        <th>{{ _lang('Flow In') }}</th>
                        <th>{{ _lang('Flow Out') }}</th>
                        <th>{{ _lang('Net Flow') }}</th>
                    </thead>
                    <tbody>
                        @php
                            $totalDebit = 0;
                            $totalCredit = 0;
                            $totalNetflow = 0;
                        @endphp
                        @foreach ($accountTransactionDetails as $statement)
                            <tr>
                                <td colspan="4" class="ledger-details">{{ $statement->transaction_date }}</td>

                                <td class="text-right">
                                    {{ $statement->credit }}
                                </td>
                                <td class="text-right">
                                    {{ $statement->debit }}
                                </td>
                                @php
                                    $netFlow = $statement->credit - $statement->debit;
                                @endphp
                                @if ($netFlow < 0)
                                    <td class="text-right" style="color: red">
                                        {{ $netFlow }}
                                    </td>
                                @else
                                    <td class="text-right" style="color: green">
                                        {{ $netFlow }}
                                    </td>
                                @endif
                                @php
                                    $totalDebit += $statement->debit;
                                    $totalCredit += $statement->credit;
                                    $totalNetflow += $totalDebit + $totalCredit;
                                @endphp
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="bg-primary">
                            <td colspan="4" class="text-right"> <strong class="text-white">{{ _lang('Total') }}</strong>
                            </td>
                            <td class="text-right"> <strong class="text-white">{{ format_currency($totalCredit) }}
                                </strong>
                            </td>
                            <td class="text-right"> <strong class="text-white">{{ format_currency($totalDebit) }}
                                </strong>
                            </td>
                            <td class="text-right"> <strong class="text-white">{{ format_currency($totalNetflow) }}
                                </strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

@endsection

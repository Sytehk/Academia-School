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
    @if (isset($cashBookEntries) && !empty($cashBookEntries) && count($cashBookEntries) > 0)
        <div class="card-border">
            @include('layouts.pdf.header')
            <div class="card-header py-3 d-flex justify-content-between">

                <div>
                    <p class="">Total Found: <strong>{{ count($cashBookEntries) ?? 0 }}</strong></p>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <th colspan="4">{{ _lang('Account Ledger') }}</th>
                        <th>{{ _lang('Credit Account') }}</th>
                        <th>{{ _lang('Debit Account') }}</th>
                    </thead>
                    <tbody>
                        @php
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp
                        @foreach ($cashBookEntries as $statement)
                            <tr>
                                <td colspan="4" class="ledger-details">
                                    {{ $statement->ledger_name }}
                                </td>

                                <td class="text-right">
                                    {{ $statement->credit }}
                                </td>
                                <td class="text-right">
                                    {{ $statement->debit }}
                                </td>
                                @php
                                    $totalDebit += $statement->debit;
                                    $totalCredit += $statement->credit;
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
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

@endsection

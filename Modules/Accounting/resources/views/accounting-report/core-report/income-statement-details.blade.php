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
    @include('layouts.pdf.header')
    <table class="table table-bordered data-table">
        <thead>
            <th>{{ _lang('Date') }}</th>
            <th>{{ _lang('Voucher ID') }}</th>
            <th>{{ _lang('Debit') }}</th>
            <th>{{ _lang('Credit') }}</th>
        </thead>
        <tbody>
            @php
                $totalDebit = 0;
                $totalCredit = 0;
            @endphp
            @foreach ($account_details as $detail)
                <tr>
                    <td>{{ $detail->created_at }}</td>
                    <td>{{ $detail->accountTransaction->voucher_id ?? 'N/A' }}</td>
                    <td class="text-right">
                        {{ $detail->debit ?? 0 }}
                    </td>
                    <td class="text-right">
                        {{ $detail->credit ?? 0 }}
                    </td>
                    @php
                        $totalDebit += $detail->debit ?? 0;
                        $totalCredit += $detail->credit ?? 0;
                    @endphp
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-primary">
                <td colspan="2" class="text-right"> <strong class="text-white">{{ _lang('Total') }}</strong> </td>
                <td class="text-right"> <strong class="text-white">{{ format_currency($totalDebit) }} </strong>
                </td>
                <td class="text-right"> <strong class="text-white">{{ format_currency($totalCredit) }} </strong>
                </td>
            </tr>
        </tfoot>
    </table>
@endsection

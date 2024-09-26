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
    </style>
@endsection
@section('content-download')
    <div class="noprint print-download-buttons">
        @include('layouts.pdf.back-button')
        @include('layouts.pdf.download-button')
        @include('layouts.pdf.print-button')
    </div>
    <div style="clear: both;"></div>
@endsection
@section('content')
    @if (isset($hrPayments) && !empty($hrPayments) && count($hrPayments) > 0)
        <div class="card-border">
            <div class="card-header py-3 d-flex justify-content-between">

                <div>
                    <p class="">Total Found: <strong>{{ count($hrPayments) ?? 0 }}</strong></p>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <th>{{ _lang('HR ID') }}</th>
                        <th>{{ _lang('Name') }}</th>
                        <th>{{ _lang('Invoice ID') }}</th>
                        <th>{{ _lang('Paid Status') }}</th>
                        <th>{{ _lang('Payment Type') }}</th>
                        <th>{{ _lang('Net Salary') }}</th>
                        <th>{{ _lang('Payable Salary') }}</th>
                        <th>{{ _lang('Paid') }}</th>
                        <th>{{ _lang('Due') }}</th>
                        <th>{{ _lang('Advance') }}</th>
                        <th>{{ _lang('Payment Date') }}</th>
                    </thead>
                    <tbody>
                        @foreach ($hrPayments as $payment)
                            <tr>
                                <td class="text-right">{{$payment->hr_id}}</td>
                                <td class="text-right">{{$payment->hr_name}}</td>
                                <td class="text-right">{{$payment->invoice_id}}</td>
                                <td class="text-right">{{$payment->is_paid}}</td>
                                <td class="text-right">{{$payment->payment_type}}</td>
                                <td class="text-right">{{$payment->net_salary}}</td>
                                <td class="text-right">{{$payment->payable_salary}}</td>
                                <td class="text-right">{{$payment->paid}}</td>
                                <td class="text-right">{{$payment->due}}</td>
                                <td class="text-right">{{$payment->advance}}</td>
                                <td class="text-right">{{$payment->payment_date}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection

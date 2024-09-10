@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Transport Fee Receipt') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="text-center mb-4">
                <h3>{{ get_option('school_name') }}</h3>
                <h5>{{ _lang('Transport Fee Receipt') }}</h5>
            </div>

            <table class="table table-bordered">
                <tr>
                    <th>{{ _lang('Invoice No') }}</th>
                    <td>{{ $collection->invoice_id }}</td>
                    <th>{{ _lang('Date') }}</th>
                    <td>{{ $collection->invoice_date }}</td>
                </tr>
                <tr>
                    <th>{{ _lang('Student') }}</th>
                    <td>{{ $collection->student?->user?->name ?? '-' }}</td>
                    <th>{{ _lang('Month') }}</th>
                    <td>{{ $collection->month }}</td>
                </tr>
                <tr>
                    <th>{{ _lang('Route') }}</th>
                    <td>{{ $collection->transportMember?->route?->route_name ?? '-' }}</td>
                    <th>{{ _lang('Stop') }}</th>
                    <td>{{ $collection->transportMember?->stop?->stop_name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>{{ _lang('Fee Amount') }}</th>
                    <td>{{ $collection->fee_amount }}</td>
                    <th>{{ _lang('Discount') }}</th>
                    <td>{{ $collection->discount }}</td>
                </tr>
                <tr>
                    <th>{{ _lang('Fine') }}</th>
                    <td>{{ $collection->fine }}</td>
                    <th>{{ _lang('Total Payable') }}</th>
                    <td>{{ $collection->total_payable }}</td>
                </tr>
                <tr>
                    <th>{{ _lang('Total Paid') }}</th>
                    <td>{{ $collection->total_paid }}</td>
                    <th>{{ _lang('Due') }}</th>
                    <td>{{ $collection->total_due }}</td>
                </tr>
            </table>

            <div class="text-center my-4">
                @php
                    $qrText = "Invoice: {$collection->invoice_id}\nStudent: {$collection->student?->user?->name}\nAmount: {$collection->total_paid}";
                @endphp
                {!! QrCode::size(100)->encoding('UTF-8')->generate($qrText) !!}
            </div>

            <div class="text-end">
                {{ _lang('Created by') }}: {{ $collection->creator->name ?? '-' }}
            </div>

            <div class="text-center mt-4 no-print">
                <button class="btn btn-primary" onclick="window.print()">{{ _lang('Print') }}</button>
            </div>
        </div>
    </div>
@endsection

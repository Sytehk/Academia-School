@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Hostel Fee Receipt') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="text-center mb-4">
                <h3>{{ get_option('school_name') }}</h3>
                <h5>{{ _lang('Hostel Fee Receipt') }}</h5>
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
                    <th>{{ _lang('Room') }}</th>
                    <td>{{ $collection->roomMember?->room?->room_number ?? '-' }}</td>
                </tr>
            </table>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>{{ _lang('Fee Head') }}</th>
                        <th>{{ _lang('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($collection->details as $detail)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $detail->fee_head)) }}</td>
                            <td>{{ $detail->amount }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>{{ _lang('Discount') }}</strong></td>
                        <td>{{ $collection->discount }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ _lang('Fine') }}</strong></td>
                        <td>{{ $collection->fine }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ _lang('Total Payable') }}</strong></td>
                        <td>{{ $collection->total_payable }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ _lang('Total Paid') }}</strong></td>
                        <td>{{ $collection->total_paid }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ _lang('Due') }}</strong></td>
                        <td>{{ $collection->total_due }}</td>
                    </tr>
                </tbody>
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

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Sale') }} — {{ $sale->invoice_id }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header"><div class="card-title">{{ _lang('Sale') }} #{{ $sale->invoice_id }}</div></div>
        <div class="card-body">
            <p><b>{{ _lang('Student') }}:</b> {{ $sale->student?->user?->name }}</p>
            <p><b>{{ _lang('Date') }}:</b> {{ $sale->sale_date->format('Y-m-d') }}</p>

            <table class="table">
                <thead>
                    <tr>
                        <th>{{ _lang('Item') }}</th>
                        <th>{{ _lang('Quantity') }}</th>
                        <th>{{ _lang('Unit Price') }}</th>
                        <th>{{ _lang('Line Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $line)
                        <tr>
                            <td>{{ $line->item?->name }}</td>
                            <td>{{ $line->quantity }}</td>
                            <td>{{ format_currency($line->unit_price) }}</td>
                            <td>{{ format_currency($line->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p><b>{{ _lang('Total Payable') }}:</b> {{ format_currency($sale->total_payable) }}</p>
            <p><b>{{ _lang('Total Paid') }}:</b> {{ format_currency($sale->total_paid) }}</p>
            <p><b>{{ _lang('Total Due') }}:</b> {{ format_currency($sale->total_due) }}</p>
            @if ($sale->note)
                <p><b>{{ _lang('Note') }}:</b> {{ $sale->note }}</p>
            @endif

            <a href="{{ route('inventory-sales.index') }}" class="btn btn-secondary">{{ _lang('Back') }}</a>
        </div>
    </div>
@endsection

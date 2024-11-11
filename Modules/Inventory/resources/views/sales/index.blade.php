@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Inventory Sales') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">{{ _lang('Inventory Sales') }}</div>
            <a href="{{ route('inventory-sales.create') }}" class="btn btn-primary btn-sm">{{ _lang('New Sale') }}</a>
        </div>
        <div class="">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <th>{{ _lang('Invoice') }}</th>
                    <th>{{ _lang('Student') }}</th>
                    <th>{{ _lang('Date') }}</th>
                    <th>{{ _lang('Payable') }}</th>
                    <th>{{ _lang('Paid') }}</th>
                    <th>{{ _lang('Due') }}</th>
                    <th>{{ _lang('Action') }}</th>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>{{ $sale->invoice_id }}</td>
                            <td>{{ $sale->student?->user?->name }}</td>
                            <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                            <td>{{ format_currency($sale->total_payable) }}</td>
                            <td>{{ format_currency($sale->total_paid) }}</td>
                            <td>
                                @if ($sale->total_due > 0)
                                    <span class="badge bg-danger">{{ format_currency($sale->total_due) }}</span>
                                @else
                                    <span class="badge bg-success">{{ _lang('Paid') }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('inventory-sales.show', $sale->id) }}" class="btn btn-sm btn-primary">{{ _lang('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-danger">{{ _lang('No sales found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3">{{ $sales->links() }}</div>
        </div>
    </div>
@endsection

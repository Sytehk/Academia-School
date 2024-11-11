@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Inventory Items') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">{{ _lang('Inventory Items') }}</div>
            <a href="{{ route('inventory-items.create') }}" class="btn btn-primary btn-sm">{{ _lang('Add Item') }}</a>
        </div>
        <div class="">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <th>{{ _lang('Name') }}</th>
                    <th>{{ _lang('Category') }}</th>
                    <th>{{ _lang('SKU') }}</th>
                    <th>{{ _lang('Cost Price') }}</th>
                    <th>{{ _lang('Selling Price') }}</th>
                    <th>{{ _lang('Stock') }}</th>
                    <th>{{ _lang('Status') }}</th>
                    <th>{{ _lang('Action') }}</th>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category?->name }}</td>
                            <td>{{ $item->sku }}</td>
                            <td>{{ format_currency($item->cost_price) }}</td>
                            <td>{{ format_currency($item->selling_price) }}</td>
                            <td>{{ $item->stock_quantity }}</td>
                            <td>
                                @if ($item->status)
                                    <span class="badge bg-success">{{ _lang('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ _lang('Inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('inventory-items.edit', $item->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i></a>
                                <form action="{{ route('inventory-items.destroy', $item->id) }}" method="post" class="d-inline" onsubmit="return confirm('{{ _lang('Delete this item?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-danger">{{ _lang('No items found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3">{{ $items->links() }}</div>
        </div>
    </div>
@endsection

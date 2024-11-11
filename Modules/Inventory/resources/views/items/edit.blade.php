@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Item') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('inventory-items.update', $item->id) }}" method="post">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Category') }}</label>
                        <select name="category_id" class="form-control" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($item->category_id == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('SKU') }}</label>
                        <input type="text" name="sku" class="form-control" value="{{ $item->sku }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Stock Quantity') }}</label>
                        <input type="number" name="stock_quantity" class="form-control" min="0" value="{{ $item->stock_quantity }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Cost Price') }}</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" min="0" value="{{ $item->cost_price }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Selling Price') }}</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" min="0" value="{{ $item->selling_price }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Status') }}</label>
                        <select name="status" class="form-control">
                            <option value="1" @selected($item->status == 1)>{{ _lang('Active') }}</option>
                            <option value="0" @selected($item->status == 0)>{{ _lang('Inactive') }}</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-4">{{ _lang('Update') }}</button>
            </form>
        </div>
    </div>
@endsection

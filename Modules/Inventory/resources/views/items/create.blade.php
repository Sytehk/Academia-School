@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Add Item') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('inventory-items.store') }}" method="post">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Category') }}</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">{{ _lang('Select Category') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Name') }}</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('SKU') }}</label>
                        <input type="text" name="sku" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Stock Quantity') }}</label>
                        <input type="number" name="stock_quantity" class="form-control" min="0" value="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Cost Price') }}</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" min="0" value="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Selling Price') }}</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" min="0" value="0" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
            </form>
        </div>
    </div>
@endsection

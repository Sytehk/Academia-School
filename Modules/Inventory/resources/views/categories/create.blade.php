@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Add Category') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('inventory-categories.store') }}" method="post">
                @csrf
                <div class="form-group">
                    <label class="my-2">{{ _lang('Name') }}</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="my-2">{{ _lang('Note') }}</label>
                    <textarea name="note" class="form-control">{{ old('note') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
            </form>
        </div>
    </div>
@endsection

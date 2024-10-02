@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Library Members List') }}
@endsection

@section('create-button')
    <a href="{{ route('librarymembers.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Member') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endsection

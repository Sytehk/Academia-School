@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Inventory Categories') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">{{ _lang('Inventory Categories') }}</div>
            <a href="{{ route('inventory-categories.create') }}" class="btn btn-primary btn-sm">{{ _lang('Add Category') }}</a>
        </div>
        <div class="">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <th>{{ _lang('Name') }}</th>
                    <th>{{ _lang('Note') }}</th>
                    <th>{{ _lang('Action') }}</th>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->note }}</td>
                            <td>
                                <a href="{{ route('inventory-categories.edit', $category->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i></a>
                                <form action="{{ route('inventory-categories.destroy', $category->id) }}" method="post" class="d-inline" onsubmit="return confirm('{{ _lang('Delete this category?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-danger">{{ _lang('No categories found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3">{{ $categories->links() }}</div>
        </div>
    </div>
@endsection

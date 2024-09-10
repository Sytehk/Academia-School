@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Vehicle Categories') }}
@endsection

@section('create-button')
    @can('vehicle_category_create')
        <a href="{{ route('vehicle-categories.create') }}" class="btn btn-primary">{{ _lang('Add Vehicle Category') }}</a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ _lang('Name') }}</th>
                        <th>{{ _lang('Status') }}</th>
                        <th>{{ _lang('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicleCategories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>
                                <span class="badge bg-{{ $category->status ? 'success' : 'danger' }}">
                                    {{ $category->status ? _lang('Active') : _lang('Inactive') }}
                                </span>
                            </td>
                            <td>
                                @can('vehicle_category_update')
                                    <a href="{{ route('vehicle-categories.edit', $category->id) }}" class="btn btn-sm btn-warning">{{ _lang('Edit') }}</a>
                                @endcan
                                @can('vehicle_category_delete')
                                    <form action="{{ route('vehicle-categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">{{ _lang('Delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">{{ _lang('No vehicle categories found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $vehicleCategories->links() }}
        </div>
    </div>
@endsection

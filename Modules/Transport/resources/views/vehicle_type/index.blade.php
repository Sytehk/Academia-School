@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Vehicle Types') }}
@endsection

@section('create-button')
    @can('vehicle_type_create')
        <a href="{{ route('vehicle-types.create') }}" class="btn btn-primary">{{ _lang('Add Vehicle Type') }}</a>
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
                    @forelse ($vehicleTypes as $type)
                        <tr>
                            <td>{{ $type->id }}</td>
                            <td>{{ $type->name }}</td>
                            <td>
                                <span class="badge bg-{{ $type->status ? 'success' : 'danger' }}">
                                    {{ $type->status ? _lang('Active') : _lang('Inactive') }}
                                </span>
                            </td>
                            <td>
                                @can('vehicle_type_update')
                                    <a href="{{ route('vehicle-types.edit', $type->id) }}" class="btn btn-sm btn-warning">{{ _lang('Edit') }}</a>
                                @endcan
                                @can('vehicle_type_delete')
                                    <form action="{{ route('vehicle-types.destroy', $type->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">{{ _lang('Delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">{{ _lang('No vehicle types found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $vehicleTypes->links() }}
        </div>
    </div>
@endsection

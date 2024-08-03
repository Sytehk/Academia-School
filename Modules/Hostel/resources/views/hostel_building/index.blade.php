@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Hostel Buildings') }}
@endsection

@section('create-button')
    @can('hostel_building_create')
        <a href="{{ route('hostel-buildings.create') }}" class="btn btn-primary">{{ _lang('Add Building') }}</a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ _lang('Hostel') }}</th>
                        <th>{{ _lang('Building Name') }}</th>
                        <th>{{ _lang('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($buildings as $building)
                        <tr>
                            <td>{{ $building->id }}</td>
                            <td>{{ $building->hostel?->hostel_name ?? '-' }}</td>
                            <td>{{ $building->building_name }}</td>
                            <td>
                                @can('hostel_building_update')
                                    <a href="{{ route('hostel-buildings.edit', $building->id) }}" class="btn btn-sm btn-warning">{{ _lang('Edit') }}</a>
                                @endcan
                                @can('hostel_building_delete')
                                    <form action="{{ route('hostel-buildings.destroy', $building->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">{{ _lang('Delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">{{ _lang('No buildings found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $buildings->links() }}
        </div>
    </div>
@endsection

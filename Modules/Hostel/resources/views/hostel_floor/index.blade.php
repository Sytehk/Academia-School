@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Hostel Floors') }}
@endsection

@section('create-button')
    @can('hostel_floor_create')
        <a href="{{ route('hostel-floors.create') }}" class="btn btn-primary">{{ _lang('Add Floor') }}</a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ _lang('Building') }}</th>
                        <th>{{ _lang('Floor Name') }}</th>
                        <th>{{ _lang('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($floors as $floor)
                        <tr>
                            <td>{{ $floor->id }}</td>
                            <td>{{ $floor->building?->building_name ?? '-' }}</td>
                            <td>{{ $floor->floor_name }}</td>
                            <td>
                                @can('hostel_floor_update')
                                    <a href="{{ route('hostel-floors.edit', $floor->id) }}" class="btn btn-sm btn-warning">{{ _lang('Edit') }}</a>
                                @endcan
                                @can('hostel_floor_delete')
                                    <form action="{{ route('hostel-floors.destroy', $floor->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">{{ _lang('Delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">{{ _lang('No floors found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $floors->links() }}
        </div>
    </div>
@endsection

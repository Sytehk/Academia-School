@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Beds') }}
@endsection

@section('create-button')
    @can('bed_create')
        <a href="{{ route('beds.create') }}" class="btn btn-primary">{{ _lang('Add Bed') }}</a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ _lang('Room') }}</th>
                        <th>{{ _lang('Bed Number') }}</th>
                        <th>{{ _lang('Status') }}</th>
                        <th>{{ _lang('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($beds as $bed)
                        <tr>
                            <td>{{ $bed->id }}</td>
                            <td>{{ $bed->room?->room_number ?? '-' }}</td>
                            <td>{{ $bed->bed_number }}</td>
                            <td>
                                <span class="badge bg-{{ $bed->status === 'vacant' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($bed->status) }}
                                </span>
                            </td>
                            <td>
                                @can('bed_update')
                                    <a href="{{ route('beds.edit', $bed->id) }}" class="btn btn-sm btn-warning">{{ _lang('Edit') }}</a>
                                @endcan
                                @can('bed_delete')
                                    <form action="{{ route('beds.destroy', $bed->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">{{ _lang('Delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">{{ _lang('No beds found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $beds->links() }}
        </div>
    </div>
@endsection

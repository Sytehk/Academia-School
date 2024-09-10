@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Helpers') }}
@endsection

@section('create-button')
    @can('helper_create')
        <a href="{{ route('helpers.create') }}" class="btn btn-primary">{{ _lang('Add Helper') }}</a>
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
                        <th>{{ _lang('Phone') }}</th>
                        <th>{{ _lang('Assigned Bus') }}</th>
                        <th>{{ _lang('Status') }}</th>
                        <th>{{ _lang('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($helpers as $helper)
                        <tr>
                            <td>{{ $helper->id }}</td>
                            <td>{{ $helper->name }}</td>
                            <td>{{ $helper->phone_number }}</td>
                            <td>{{ $helper->bus?->bus_number ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $helper->status ? 'success' : 'danger' }}">
                                    {{ $helper->status ? _lang('Active') : _lang('Inactive') }}
                                </span>
                            </td>
                            <td>
                                @can('helper_update')
                                    <a href="{{ route('helpers.edit', $helper->id) }}" class="btn btn-sm btn-warning">{{ _lang('Edit') }}</a>
                                @endcan
                                @can('helper_delete')
                                    <form action="{{ route('helpers.destroy', $helper->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">{{ _lang('Delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">{{ _lang('No helpers found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $helpers->links() }}
        </div>
    </div>
@endsection

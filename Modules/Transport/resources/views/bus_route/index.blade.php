@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Bus Routes') }}
@endsection

@section('create-button')
    <a href="{{ route('bus-routes.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Route') }}
    </a>
@endsection

@section('content')
    <div class="table-responsive">

        <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ _lang('SL') }}</th>
                    <th>{{ _lang('Route Name') }}</th>
                    <th>{{ _lang('Start Location') }}</th>
                    <th>{{ _lang('End Location') }}</th>
                    <th>{{ _lang('Distance') }}</th>
                    <th>{{ _lang('Estimated Time') }}</th>
                    <th>{{ _lang('Status') }}</th>
                    <th>{{ _lang('Action') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($busRoutes as $key => $route)
                    <tr>
                        <td>{{ $key + 1 }}</td>

                        <td>{{ $route->route_name }}</td>

                        <td>{{ $route->start_location }}</td>

                        <td>{{ $route->end_location }}</td>

                        <td>{{ $route->total_distance }}</td>

                        <td>{{ $route->estimated_time }}</td>

                        <td>
                            @if ((int) $route->status === 1)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('bus-routes.edit', $route->id) }}" class="btn btn-sm btn-primary">
                                Edit
                            </a>

                            <form action="{{ route('bus-routes.destroy', $route->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete Route?')">
                                    Delete
                                </button>
                            </form>
                        </td>

                    </tr>
                @endforeach
            </tbody>

        </table>

    </div>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Bus Stops') }}
@endsection

@section('create-button')
    <a href="{{ route('bus-stops.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Stop') }}
    </a>
@endsection

@section('content')
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Stop Name</th>
                <th>Route Name</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Order</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($stops as $stop)
                <tr>
                    <td>{{ $stop->id }}</td>
                    <td>{{ $stop->stop_name }}</td>

                    {{-- JOINED NAME --}}
                    <td>{{ $stop->route_name ?? 'N/A' }}</td>

                    <td>{{ $stop->latitude }}</td>
                    <td>{{ $stop->longitude }}</td>
                    <td>{{ $stop->stop_order }}</td>

                    <td>
                        <a href="{{ route('bus-stops.edit', $stop->id) }}" class="btn btn-sm btn-warning">
                            Edit
                        </a>

                        <form action="{{ route('bus-stops.destroy', $stop->id) }}" method="POST"
                            style="display:inline-block;">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

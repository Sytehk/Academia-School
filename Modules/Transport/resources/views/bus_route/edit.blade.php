@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Bus Route') }}
@endsection

@section('content')
    <form action="{{ route('bus-routes.update', $route->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="row">

            <div class="col-md-6">
                <label>{{ _lang('Route Name') }}</label>
                <input type="text" name="route_name" value="{{ $route->route_name }}" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label>{{ _lang('Assigned Vehicle') }}</label>
                <select name="bus_id" class="form-control">
                    <option value="">{{ _lang('Select Bus') }}</option>
                    @foreach ($buses as $bus)
                        <option value="{{ $bus->id }}" {{ $route->bus_id == $bus->id ? 'selected' : '' }}>{{ $bus->bus_number }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mt-3">
                <label>{{ _lang('Start Location') }}</label>
                <input type="text" name="start_location" value="{{ $route->start_location }}" class="form-control"
                    required>
            </div>

            <div class="col-md-6 mt-3">
                <label>{{ _lang('End Location') }}</label>
                <input type="text" name="end_location" value="{{ $route->end_location }}" class="form-control" required>
            </div>

            <div class="col-md-3 mt-3">
                <label>{{ _lang('Total Distance') }}</label>
                <input type="text" name="total_distance" value="{{ $route->total_distance }}" class="form-control">
            </div>

            <div class="col-md-3 mt-3">
                <label>{{ _lang('Estimated Time') }}</label>
                <input type="time" name="estimated_time"
                    value="{{ $route->estimated_time ? substr($route->estimated_time, 0, 5) : '' }}" class="form-control">
            </div>

            <div class="col-md-3 mt-3">
                <label>{{ _lang('Status') }}</label>
                <select name="status" class="form-control">
                    <option value="1" {{ $route->status == '1' ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="0" {{ $route->status == '0' ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
            </div>

        </div>

        <div class="mt-4">
            <button class="btn btn-success">
                Update
            </button>
        </div>

    </form>
@endsection

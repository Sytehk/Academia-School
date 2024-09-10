@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Bus Route') }}
@endsection

@section('content')
    <form action="{{ route('bus-routes.store') }}" method="POST">
        @csrf

        <div class="row">

            <div class="col-md-6">
                <label>{{ _lang('Route Name') }}</label>
                <input type="text" name="route_name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label>{{ _lang('Assigned Vehicle') }}</label>
                <select name="bus_id" class="form-control">
                    <option value="">{{ _lang('Select Bus') }}</option>
                    @foreach ($buses as $bus)
                        <option value="{{ $bus->id }}">{{ $bus->bus_number }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mt-3">
                <label>{{ _lang('Start Location') }}</label>
                <input type="text" name="start_location" class="form-control" required>
            </div>

            <div class="col-md-6 mt-3">
                <label>{{ _lang('End Location') }}</label>
                <input type="text" name="end_location" class="form-control" required>
            </div>

            <div class="col-md-3 mt-3">
                <label>{{ _lang('Total Distance') }}</label>
                <input type="number" name="total_distance" class="form-control">
            </div>

            <div class="col-md-3 mt-3">
                <label>{{ _lang('Estimated Time') }}</label>
                <input type="time" name="estimated_time" class="form-control">
            </div>

            <div class="col-md-3 mt-3">
                <label>{{ _lang('Status') }}</label>
                <select name="status" class="form-control">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

        </div>

        <div class="mt-4">
            <button class="btn btn-primary">
                Save
            </button>
        </div>

    </form>
@endsection

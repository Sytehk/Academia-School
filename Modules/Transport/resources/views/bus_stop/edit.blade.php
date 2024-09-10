@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Bus Stop') }}
@endsection

@section('content')
    <form action="{{ route('bus-stops.update', $stop->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="row">

            <div class="col-lg-6">
                <label class="my-2">Stop Name</label>
                <input type="text" name="stop_name" class="form-control" value="{{ $stop->stop_name }}" required>
            </div>

            <div class="col-lg-6">
                <label class="my-2">Select Route</label>

                <select name="route_id" class="form-control" required>
                    <option value="">-- Select Route --</option>

                    @foreach ($routes as $route)
                        <option value="{{ $route->id }}" {{ $stop->route_id == $route->id ? 'selected' : '' }}>
                            {{ $route->route_name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="col-lg-6">
                <label class="my-2">Latitude</label>
                <input type="text" name="latitude" class="form-control" value="{{ $stop->latitude }}" required>
            </div>

            <div class="col-lg-6">
                <label class="my-2">Longitude</label>
                <input type="text" name="longitude" class="form-control" value="{{ $stop->longitude }}" required>
            </div>

            <div class="col-lg-6">
                <label class="my-2">Stop Order</label>
                <input type="number" name="stop_order" class="form-control" value="{{ $stop->stop_order }}">
            </div>

        </div>

        <button class="btn btn-success mt-5">
            Update Bus Stop
        </button>

    </form>
@endsection

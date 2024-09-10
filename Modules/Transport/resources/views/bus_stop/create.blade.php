@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Bus Stop') }}
@endsection

@section('content')
    <form action="{{ route('bus-stops.store') }}" method="POST">
        @csrf

        <div class="row">

            {{-- Stop Name --}}
            <div class="col-lg-6">
                <label class="my-2">Stop Name</label>
                <input type="text" name="stop_name" class="form-control" required>
            </div>

            {{-- Route ID --}}
            <div class="col-lg-6">
                <label class="my-2">Select Route</label>

                <select name="route_id" class="form-control" required>
                    <option value="">-- Select Route --</option>

                    @foreach ($busRoutes as $route)
                        <option value="{{ $route->id }}">
                            {{ $route->route_name }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- Latitude --}}
            <div class="col-lg-6">
                <label class="my-2">Latitude</label>
                <input type="number" step="0.00000001" name="latitude" class="form-control" placeholder="23.810331"
                    required>
            </div>

            {{-- Longitude --}}
            <div class="col-lg-6">
                <label class="my-2">Longitude</label>
                <input type="number" step="0.00000001" name="longitude" class="form-control" placeholder="90.412521"
                    required>
            </div>

            {{-- Stop Order --}}
            <div class="col-lg-6">
                <label class="my-2">Stop Order</label>
                <input type="number" name="stop_order" class="form-control">
            </div>

        </div>

        <button class="btn btn-primary mt-5">
            Create Bus Stop
        </button>

    </form>
@endsection

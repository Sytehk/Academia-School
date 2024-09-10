@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Update Driver') }}
@endsection

@section('content')
    <form action="{{ route('drivers.update', $driver->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="row">

            {{-- Name --}}
            <div class="col-lg-6 mb-3">
                <label>{{ _lang('Name') }}</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $driver->name) }}">
            </div>

            {{-- Phone Number --}}
            <div class="col-lg-6 mb-3">
                <label>{{ _lang('Phone') }}</label>
                <input type="text" name="phone_number" class="form-control"
                    value="{{ old('phone_number', $driver->phone_number) }}">
            </div>

            {{-- License Number --}}
            <div class="col-lg-6 mb-3">
                <label>{{ _lang('License No') }}</label>
                <input type="text" name="license_number" class="form-control"
                    value="{{ old('license_number', $driver->license_number) }}">
            </div>

            {{-- Assigned Bus --}}
            <div class="col-lg-6 mb-3">
                <label>{{ _lang('Assign Bus') }}</label>
                <select name="assigned_bus_id" class="form-control">

                    <option value="">
                        {{ _lang('Select Bus') }}
                    </option>

                    @foreach ($buses as $bus)
                        <option value="{{ $bus->id }}"
                            {{ old('assigned_bus_id', $driver->assigned_bus_id) == $bus->id ? 'selected' : '' }}>
                            {{ $bus->bus_number }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- Status --}}
            <div class="col-lg-6 mb-3">
                <label>{{ _lang('Status') }}</label>
                <select name="status" class="form-control">

                    <option value="1" {{ old('status', $driver->status) == 1 ? 'selected' : '' }}>
                        {{ _lang('Active') }}
                    </option>

                    <option value="0" {{ old('status', $driver->status) == 0 ? 'selected' : '' }}>
                        {{ _lang('Inactive') }}
                    </option>

                </select>
            </div>

        </div>

        <button class="btn btn-primary mt-4">
            {{ _lang('Update Driver') }}
        </button>

    </form>
@endsection

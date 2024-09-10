@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Driver') }}
@endsection

@section('content')
    <form action="{{ route('drivers.store') }}" method="POST">
        @csrf

        <div class="row">

            {{-- Name --}}
            <div class="col-lg-6 mb-3">
                <label>{{ _lang('Name') }} *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            {{-- Phone Number --}}
            <div class="col-lg-6 mb-3">
                <label>{{ _lang('Phone') }} *</label>
                <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number') }}" required>
            </div>

            {{-- License Number --}}
            <div class="col-lg-6 mb-3">
                <label>{{ _lang('License No') }} *</label>
                <input type="text" name="license_number" class="form-control" value="{{ old('license_number') }}"
                    required>
            </div>

            {{-- Assigned Bus --}}
            <div class="col-lg-6 mb-3">
                <label>{{ _lang('Assign Bus') }}</label>
                <select name="assigned_bus_id" class="form-control">
                    <option value="">{{ _lang('Select Bus') }}</option>

                    @foreach ($buses as $bus)
                        <option value="{{ $bus->id }}" {{ old('assigned_bus_id') == $bus->id ? 'selected' : '' }}>
                            {{ $bus->bus_number }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- Status --}}
            <div class="col-lg-6 mb-3">
                <label>{{ _lang('Status') }}</label>
                <select name="status" class="form-control">
                    <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
            </div>

        </div>

        <button class="btn btn-primary mt-4">
            {{ _lang('Create Driver') }}
        </button>

    </form>
@endsection

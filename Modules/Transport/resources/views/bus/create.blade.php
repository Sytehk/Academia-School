@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Bus') }}
@endsection

@section('content')
    <form action="{{ route('buses.store') }}" method="POST">
        @csrf

        <div class="row">

            {{-- Bus Number --}}
            <div class="col-md-6 mb-3">
                <label>{{ _lang('Bus Number') }} *</label>
                <input type="text" name="bus_number" class="form-control" value="{{ old('bus_number') }}" required>

                @error('bus_number')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Model --}}
            <div class="col-md-6 mb-3">
                <label>{{ _lang('Model') }}</label>
                <input type="text" name="model" class="form-control" value="{{ old('model') }}">
            </div>

            {{-- Capacity --}}
            <div class="col-md-6 mb-3">
                <label>{{ _lang('Capacity') }} *</label>
                <input type="number" name="capacity" class="form-control" value="{{ old('capacity') }}" required>

                @error('capacity')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Status --}}
            <div class="col-md-6 mb-3">
                <label>{{ _lang('Status') }}</label>
                <select name="status" class="form-control">
                    <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                {{ _lang('Create Bus') }}
            </button>
        </div>

    </form>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Vehicle Type') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('vehicle-types.update', $vehicleType->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $vehicleType->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Status') }}</label>
                        <select name="status" class="form-control">
                            <option value="1" @selected($vehicleType->status == 1)>{{ _lang('Active') }}</option>
                            <option value="0" @selected($vehicleType->status == 0)>{{ _lang('Inactive') }}</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary mt-4">{{ _lang('Update') }}</button>
            </form>
        </div>
    </div>
@endsection

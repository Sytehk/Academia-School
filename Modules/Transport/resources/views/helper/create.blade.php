@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Add Helper') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('helpers.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Phone Number') }}</label>
                        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Assigned Bus') }}</label>
                        <select name="assigned_bus_id" class="form-control">
                            <option value="">{{ _lang('Select Bus') }}</option>
                            @foreach ($buses as $bus)
                                <option value="{{ $bus->id }}" @selected(old('assigned_bus_id') == $bus->id)>{{ $bus->bus_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Status') }}</label>
                        <select name="status" class="form-control">
                            <option value="1">{{ _lang('Active') }}</option>
                            <option value="0">{{ _lang('Inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">{{ _lang('Address') }}</label>
                        <textarea name="address" class="form-control">{{ old('address') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Emergency Contact Name') }}</label>
                        <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Emergency Contact Phone') }}</label>
                        <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone') }}">
                    </div>
                </div>
                <button class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
            </form>
        </div>
    </div>
@endsection

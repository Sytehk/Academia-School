@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Building') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('hostel-buildings.update', $building->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Hostel') }}</label>
                        <select name="hostel_id" class="form-control">
                            <option value="" @selected(is_null($building->hostel_id))>{{ _lang('Global (applies to all hostels)') }}</option>
                            @foreach ($hostels as $hostel)
                                <option value="{{ $hostel->id }}" @selected($building->hostel_id == $hostel->id)>{{ $hostel->hostel_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Building Name') }}</label>
                        <input type="text" name="building_name" class="form-control" value="{{ $building->building_name }}" required>
                    </div>
                </div>
                <button class="btn btn-primary mt-4">{{ _lang('Update') }}</button>
            </form>
        </div>
    </div>
@endsection

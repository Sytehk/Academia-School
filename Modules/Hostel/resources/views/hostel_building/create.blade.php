@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Add Building') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('hostel-buildings.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Hostel') }}</label>
                        <select name="hostel_id" class="form-control">
                            <option value="">{{ _lang('Global (applies to all hostels)') }}</option>
                            @foreach ($hostels as $hostel)
                                <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Building Name') }}</label>
                        <input type="text" name="building_name" class="form-control" required>
                    </div>
                </div>
                <button class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
            </form>
        </div>
    </div>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Floor') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('hostel-floors.update', $floor->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Building') }}</label>
                        <select name="building_id" class="form-control">
                            <option value="" @selected(is_null($floor->building_id))>{{ _lang('Global (applies to all buildings)') }}</option>
                            @foreach ($buildings as $building)
                                <option value="{{ $building->id }}" @selected($floor->building_id == $building->id)>{{ $building->building_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Floor Name') }}</label>
                        <input type="text" name="floor_name" class="form-control" value="{{ $floor->floor_name }}" required>
                    </div>
                </div>
                <button class="btn btn-primary mt-4">{{ _lang('Update') }}</button>
            </form>
        </div>
    </div>
@endsection

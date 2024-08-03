@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Bed') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('beds.update', $bed->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Room') }}</label>
                        <select name="room_id" class="form-control" required>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected($bed->room_id == $room->id)>{{ $room->room_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Bed Number') }}</label>
                        <input type="text" name="bed_number" class="form-control" value="{{ $bed->bed_number }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Status') }}</label>
                        <select name="status" class="form-control">
                            <option value="vacant" @selected($bed->status == 'vacant')>{{ _lang('Vacant') }}</option>
                            <option value="occupied" @selected($bed->status == 'occupied')>{{ _lang('Occupied') }}</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary mt-4">{{ _lang('Update') }}</button>
            </form>
        </div>
    </div>
@endsection

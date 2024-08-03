@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Add Bed') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('beds.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Room') }}</label>
                        <select name="room_id" class="form-control" required>
                            <option value="">{{ _lang('Select Room') }}</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->room_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Bed Number') }}</label>
                        <input type="text" name="bed_number" class="form-control" required>
                    </div>
                </div>
                <button class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
            </form>
        </div>
    </div>
@endsection

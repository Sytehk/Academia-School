@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Room') }}
@endsection

@section('content')
    <form action="{{ route('rooms.update', $room->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="row">

            <div class="col-md-6 mb-3">
                <label>{{ _lang('Hostel Category') }}
                    <span class="text-danger">*</span>
                </label>

                <select name="hostel_category_id" class="form-control" required>

                    @foreach ($hostelCategories as $category)
                        <option value="{{ $category->id }}"
                            {{ $room->hostel_category_id == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach

                </select>

                @error('hostel_category_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label>{{ _lang('Room Number') }}
                    <span class="text-danger">*</span>
                </label>

                <input type="text" name="room_number" class="form-control"
                    value="{{ old('room_number', $room->room_number) }}" required>

                @error('room_number')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label>{{ _lang('Capacity') }}
                    <span class="text-danger">*</span>
                </label>

                <input type="number" name="capacity" class="form-control" value="{{ old('capacity', $room->capacity) }}"
                    required>

                @error('capacity')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

        </div>

        <button type="submit" class="btn btn-primary">
            {{ _lang('Update Room') }}
        </button>
    </form>
@endsection

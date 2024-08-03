@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Room') }}
@endsection

@section('content')
    <form action="{{ route('rooms.store') }}" method="POST">
        @csrf

        <div class="row">

            <div class="col-md-6 mb-3">
                <label>{{ _lang('Hostel Category') }}
                    <span class="text-danger">*</span>
                </label>

                <select name="hostel_category_id" class="form-control" required>

                    <option value="">
                        {{ _lang('Select Category') }}
                    </option>

                    @foreach ($hostelCategories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->standard }}
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

                <input type="text" name="room_number" class="form-control" value="{{ old('room_number') }}" required>

                @error('room_number')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label>{{ _lang('Capacity') }}
                    <span class="text-danger">*</span>
                </label>

                <input type="number" name="capacity" class="form-control" value="{{ old('capacity') }}" required>

                @error('capacity')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

        </div>

        <button type="submit" class="btn btn-primary">
            {{ _lang('Create Room') }}
        </button>
    </form>
@endsection

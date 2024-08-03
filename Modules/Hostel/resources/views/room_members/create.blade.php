@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Room Member') }}
@endsection

@section('content')
    <form action="{{ route('room-members.store') }}" method="POST">
        @csrf

        <div class="row">

            <div class="col-md-6 mb-3">
                <label>{{ _lang('Student') }}</label>

                <select name="student_id" class="form-control" required>

                    <option value="">
                        Select Student
                    </option>

                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">
                            {{ $student->first_name }}
                            ({{ $student->phone }})
                        </option>
                    @endforeach
                </select>

                @error('student_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label>{{ _lang('Room') }}</label>

                <select name="room_id" class="form-control" required>

                    <option value="">
                        Select Room
                    </option>

                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}">
                            {{ $room->room_number }}
                        </option>
                    @endforeach
                </select>

                @error('room_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

        </div>

        <button type="submit" class="btn btn-primary">
            Create Room Member
        </button>

    </form>
@endsection

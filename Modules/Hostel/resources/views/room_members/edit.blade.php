@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Room Member') }}
@endsection

@section('content')
    <form action="{{ route('room-members.update', $member->id) }}" method="POST">

        @csrf
        @method('PATCH')

        <div class="row">

            <div class="col-md-6 mb-3">
                <label>{{ _lang('Student') }}</label>

                <select name="student_id" class="form-control" required>

                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" {{ $member->student_id == $student->id ? 'selected' : '' }}>
                            {{ $student->first_name }}
                            ({{ $student->phone }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>{{ _lang('Room') }}</label>

                <select name="room_id" class="form-control" required>

                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}" {{ $member->room_id == $room->id ? 'selected' : '' }}>
                            {{ $room->room_number }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <button type="submit" class="btn btn-primary">
            Update Room Member
        </button>

    </form>
@endsection

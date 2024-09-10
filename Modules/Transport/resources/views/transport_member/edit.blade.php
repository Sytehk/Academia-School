@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Update Transport Member') }}
@endsection

@section('content')
    <form action="{{ route('transport-members.update', $member->id) }}" method="POST">

        @csrf
        @method('PATCH')

        <div class="row">


            {{-- STUDENT --}}
            <div class="col-lg-6">
                <label class="my-2">Student</label>

                <select name="student_id" class="form-control" required>
                    <option value="">-- Select Student --</option>

                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" {{ $member->student_id == $student->id ? 'selected' : '' }}>

                            {{ $student->first_name }} {{ $student->last_name }}

                        </option>
                    @endforeach

                </select>
            </div>



            {{-- ROUTE --}}
            <div class="col-lg-6">
                <label class="my-2">Route</label>

                <select name="assigned_route_id" class="form-control" required>
                    <option value="">-- Select Route --</option>

                    @foreach ($routes as $route)
                        <option value="{{ $route->id }}"
                            {{ $member->assigned_route_id == $route->id ? 'selected' : '' }}>

                            {{ $route->route_name }}

                        </option>
                    @endforeach

                </select>
            </div>



            {{-- STOP --}}
            <div class="col-lg-6">
                <label class="my-2">Stop</label>

                <select name="assigned_stop_id" class="form-control" required>
                    <option value="">-- Select Stop --</option>

                    @foreach ($stops as $stop)
                        <option value="{{ $stop->id }}" {{ $member->assigned_stop_id == $stop->id ? 'selected' : '' }}>

                            {{ $stop->stop_name }}

                        </option>
                    @endforeach

                </select>
            </div>



            {{-- FARE --}}
            <div class="col-lg-6">
                <label class="my-2">Fare Amount</label>

                <input type="number" step="0.01" name="fare_amount" class="form-control"
                    value="{{ $member->fare_amount }}" required>
            </div>



            {{-- STATUS --}}
            <div class="col-lg-6">
                <label class="my-2">Status</label>

                <select name="status" class="form-control" required>

                    <option value="1" {{ $member->status == 1 ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="2" {{ $member->status == 2 ? 'selected' : '' }}>
                        Inactive
                    </option>

                </select>
            </div>


        </div>



        <button type="submit" class="btn btn-success mt-4">
            Update Transport Member
        </button>

    </form>
@endsection

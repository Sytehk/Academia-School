@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Transport Member') }}
@endsection

@section('content')
    <form action="{{ route('transport-members.store') }}" method="POST">

        @csrf

        <div class="row">


            {{-- STUDENT --}}
            <div class="col-lg-6">
                <label class="my-2">Student</label>

                <select name="student_id" class="form-control" required>
                    <option value="">-- Select Student --</option>

                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">
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
                        <option value="{{ $route->id }}">
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
                        <option value="{{ $stop->id }}">
                            {{ $stop->stop_name }}
                        </option>
                    @endforeach

                </select>
            </div>



            {{-- FARE --}}
            <div class="col-lg-6">
                <label class="my-2">Fare Amount</label>

                <input type="number" step="0.01" name="fare_amount" class="form-control" placeholder="Enter fare amount"
                    required>
            </div>



            {{-- STATUS --}}
            <div class="col-lg-6">
                <label class="my-2">Status</label>

                <select name="status" class="form-control">

                    <option value="1">Active</option>
                    <option value="2">Inactive</option>

                </select>

            </div>


        </div>


        <button class="btn btn-primary mt-4">
            Create Member
        </button>

    </form>
@endsection

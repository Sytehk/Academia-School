@extends('layouts.backend')


@section('content')
    <form method="POST" action="{{ route('hostel-members.store') }}">

        @csrf



        <select name="student_id" class="form-control">


            <option>Select Student</option>


            @foreach ($students as $student)
                <option value="{{ $student->id }}">

                    {{ $student->name }}

                </option>
            @endforeach


        </select>




        <select name="hostel_category_id" class="form-control mt-3">


            <option>Select Category</option>


            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}">

                    {{ $cat->hostel?->hostel_name ?? 'Global' }}
                    -
                    {{ $cat->standard }}

                </option>
            @endforeach


        </select>



        <button class="btn btn-primary mt-4">

            Save

        </button>


    </form>
@endsection

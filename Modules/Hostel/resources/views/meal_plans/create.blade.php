@extends('layouts.backend')



@section('content')
    <form action="{{ route('meal-plans.store') }}" method="POST">


        @csrf



        <label>
            Student
        </label>


        <select name="student_id" class="form-control">


            <option>Select Student</option>


            @foreach ($students as $student)
                <option value="{{ $student->id }}">


                    {{ $student->user->name ?? '' }}


                </option>
            @endforeach


        </select>




        <label class="mt-3">

            Meal

        </label>


        <select name="meal_id" class="form-control">


            <option>Select Meal</option>


            @foreach ($meals as $meal)
                <option value="{{ $meal->id }}">


                    {{ $meal->meal_name }}


                </option>
            @endforeach


        </select>




        <label class="mt-3">

            Date

        </label>


        <input type="date" name="date" class="form-control">



        <button class="btn btn-primary mt-5">

            Save

        </button>


    </form>
@endsection

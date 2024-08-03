@extends('layouts.backend')



@section('content')
    <form action="{{ route('meal-plans.update', $mealPlan->id) }}" method="POST">


        @csrf

        @method('PUT')




        <label>

            Student

        </label>



        <select name="student_id" class="form-control">



            @foreach ($students as $student)
                <option value="{{ $student->id }}" @if ($student->id == $mealPlan->student_id) selected @endif>


                    {{ $student->user->name ?? '' }}


                </option>
            @endforeach



        </select>





        <label class="mt-3">

            Meal

        </label>



        <select name="meal_id" class="form-control">


            @foreach ($meals as $meal)
                <option value="{{ $meal->id }}" @if ($meal->id == $mealPlan->meal_id) selected @endif>


                    {{ $meal->meal_name }}


                </option>
            @endforeach



        </select>





        <label class="mt-3">

            Date

        </label>


        <input type="date" name="date" class="form-control" value="{{ $mealPlan->date }}">





        <button class="btn btn-primary mt-5">

            Update

        </button>



    </form>
@endsection

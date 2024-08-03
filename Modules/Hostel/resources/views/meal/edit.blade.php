@extends('layouts.backend')


@section('page_title_breadcrumb')
    Update Meal
@endsection



@section('content')
    <form action="{{ route('meals.update', $meal->id) }}" method="POST">


        @csrf

        @method('PUT')




        <label>
            Meal Name
        </label>


        <input type="text" name="meal_name" value="{{ $meal->meal_name }}" class="form-control">





        <label class="mt-3">

            Meal Type

        </label>



        <input type="text" name="meal_type" value="{{ $meal->meal_type }}" class="form-control">





        <button class="btn btn-primary mt-5">

            Update Meal

        </button>



    </form>
@endsection

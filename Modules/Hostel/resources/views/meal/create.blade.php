@extends('layouts.backend')


@section('page_title_breadcrumb')
    Create Meal
@endsection



@section('content')
    <form action="{{ route('meals.store') }}" method="POST">


        @csrf



        <div class="form-group">


            <label>
                Meal Name
            </label>


            <input type="text" name="meal_name" class="form-control" required>


        </div>




        <div class="form-group mt-3">


            <label>
                Meal Type
            </label>


            <input type="text" name="meal_type" class="form-control" required>


        </div>




        <button class="btn btn-primary mt-5">

            Save Meal

        </button>



    </form>
@endsection

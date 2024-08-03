@extends('layouts.backend')


@section('create-button')
    <a href="{{ route('meal-plans.create') }}" class="btn btn-primary btn-sm">

        Add Meal Plan

    </a>
@endsection




@section('content')
    <table class="table">


        <thead>

            <tr>

                <th>SL</th>
                <th>Student</th>
                <th>Meal</th>
                <th>Date</th>
                <th>Action</th>

            </tr>

        </thead>


        <tbody>


            @foreach ($mealPlans as $key => $plan)
                <tr>


                    <td>

                        {{ $mealPlans->firstItem() + $key }}

                    </td>



                    <td>

                        {{ $plan->student->user->name ?? '' }}

                    </td>



                    <td>

                        {{ $plan->meal->meal_name ?? '' }}

                    </td>



                    <td>

                        {{ $plan->date }}

                    </td>



                    <td>


                        <a href="{{ route('meal-plans.edit', $plan->id) }}" class="btn btn-warning btn-sm">

                            Edit

                        </a>



                        <form action="{{ route('meal-plans.destroy', $plan->id) }}" method="POST" style="display:inline">


                            @csrf

                            @method('DELETE')


                            <button class="btn btn-danger btn-sm">

                                Delete

                            </button>


                        </form>


                    </td>


                </tr>
            @endforeach


        </tbody>


    </table>


    {{ $mealPlans->links() }}
@endsection

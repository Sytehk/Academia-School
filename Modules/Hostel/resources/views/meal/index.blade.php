@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Meals') }}
@endsection



@section('create-button')
    <a href="{{ route('meals.create') }}" class="btn btn-sm btn-primary">

        Add Meal

    </a>
@endsection




@section('content')
    <div class="table-responsive">


        <table class="table align-middle table-row-dashed">


            <thead>

                <tr>

                    <th>SL</th>

                    <th>Meal Name</th>

                    <th>Type</th>

                    <th>Created</th>

                    <th>Action</th>


                </tr>


            </thead>



            <tbody>


                @foreach ($meals as $key => $meal)
                    <tr>


                        <td>

                            {{ $meals->firstItem() + $key }}

                        </td>



                        <td>

                            {{ $meal->meal_name }}

                        </td>



                        <td>

                            {{ $meal->meal_type }}

                        </td>



                        <td>

                            {{ $meal->created_at->format('d-m-Y') }}

                        </td>



                        <td>



                            <a href="{{ route('meals.edit', $meal->id) }}" class="btn btn-sm btn-warning">

                                Edit

                            </a>




                            <form action="{{ route('meals.destroy', $meal->id) }}" method="POST" style="display:inline">


                                @csrf

                                @method('DELETE')



                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete Meal?')">

                                    Delete

                                </button>


                            </form>


                        </td>


                    </tr>
                @endforeach


            </tbody>


        </table>


        {{ $meals->links() }}


    </div>
@endsection

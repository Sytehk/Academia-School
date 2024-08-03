@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Hostel Category') }}
@endsection


@section('create-button')
    <a href="{{ route('hostel-categories.create') }}" class="btn btn-sm btn-primary">

        {{ _lang('Add Category') }}

    </a>
@endsection



@section('content')
    <div class="table-responsive">

        <table class="table align-middle table-row-dashed fs-6 gy-5">

            <thead>

                <tr>

                    <th>SL</th>

                    <th>Hostel</th>

                    <th>Standard</th>

                    <th>Fee</th>

                    <th>Note</th>

                    <th>Action</th>

                </tr>

            </thead>


            <tbody>


                @foreach ($categories as $key => $category)
                    <tr>


                        <td>
                            {{ $categories->firstItem() + $key }}
                        </td>


                        <td>
                            {{ $category->hostel?->hostel_name ?? 'Global' }}
                        </td>


                        <td>
                            {{ $category->standard }}
                        </td>


                        <td>
                            {{ $category->hostel_fee }}
                        </td>


                        <td>
                            {{ $category->note }}
                        </td>


                        <td>


                            <a href="{{ route('hostel-categories.edit', $category->id) }}" class="btn btn-sm btn-warning">

                                Edit

                            </a>



                            <form action="{{ route('hostel-categories.destroy', $category->id) }}" method="POST"
                                style="display:inline">

                                @csrf
                                @method('DELETE')


                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">

                                    Delete

                                </button>


                            </form>


                        </td>


                    </tr>
                @endforeach


            </tbody>

        </table>


        {{ $categories->links() }}


    </div>
@endsection

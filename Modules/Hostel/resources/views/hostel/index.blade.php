@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Hostel') }}
@endsection



@section('create-button')
    <a href="{{ route('hostels.create') }}" class="btn btn-sm btn-primary">

        {{ _lang('Add Hostel') }}

    </a>
@endsection




@section('content')
    <div class="table-responsive">


        <table class="table align-middle table-row-dashed fs-6 gy-5">


            <thead>

                <tr>

                    <th>SL</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Address</th>
                    <th>Note</th>
                    <th>Action</th>

                </tr>

            </thead>



            <tbody>


                @foreach ($hostels as $key => $hostel)
                    <tr>


                        <td>
                            {{ $hostels->firstItem() + $key }}
                        </td>


                        <td>
                            {{ $hostel->hostel_name }}
                        </td>


                        <td>
                            {{ $hostel->type }}
                        </td>


                        <td>
                            {{ $hostel->address }}
                        </td>


                        <td>
                            {{ $hostel->note }}
                        </td>



                        <td>


                            <a href="{{ route('hostels.edit', $hostel->id) }}" class="btn btn-sm btn-warning">

                                Edit

                            </a>




                            <form action="{{ route('hostels.destroy', $hostel->id) }}" method="POST" style="display:inline">


                                @csrf

                                @method('DELETE')


                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete Hostel?')">

                                    Delete

                                </button>


                            </form>



                        </td>


                    </tr>
                @endforeach


            </tbody>


        </table>


        {{ $hostels->links() }}


    </div>
@endsection

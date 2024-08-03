@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Create Hostel') }}
@endsection



@section('content')
    <form action="{{ route('hostels.store') }}" method="POST">


        @csrf



        <label class="mt-2">
            Hostel Name
        </label>


        <input type="text" name="hostel_name" class="form-control">



        <label class="mt-3">
            Type
        </label>


        <input type="text" name="type" class="form-control">



        <label class="mt-3">
            Address
        </label>


        <textarea name="address" class="form-control"></textarea>



        <label class="mt-3">
            Note
        </label>


        <textarea name="note" class="form-control"></textarea>




        <button class="btn btn-primary mt-5">

            Save Hostel

        </button>


    </form>
@endsection

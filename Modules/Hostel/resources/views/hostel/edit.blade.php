@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Update Hostel') }}
@endsection



@section('content')
    <form action="{{ route('hostels.update', $hostel->id) }}" method="POST">


        @csrf

        @method('PUT')



        <label>
            Hostel Name
        </label>


        <input class="form-control" name="hostel_name" value="{{ $hostel->hostel_name }}">



        <label class="mt-3">
            Type
        </label>


        <input class="form-control" name="type" value="{{ $hostel->type }}">



        <label class="mt-3">
            Address
        </label>


        <textarea class="form-control" name="address">{{ $hostel->address }}</textarea>



        <label class="mt-3">
            Note
        </label>


        <textarea class="form-control" name="note">{{ $hostel->note }}</textarea>




        <button class="btn btn-primary mt-5">

            Update Hostel

        </button>



    </form>
@endsection

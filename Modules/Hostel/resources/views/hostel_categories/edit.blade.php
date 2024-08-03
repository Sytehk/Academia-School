@extends('layouts.backend')


@section('content')
    <form action="{{ route('hostel-categories.update', $category->id) }}" method="POST">

        @csrf
        @method('PUT')



        <select name="hostel_id" class="form-control">

            <option value="" @if (is_null($category->hostel_id)) selected @endif>Global (applies to all hostels)</option>

            @foreach ($hostels as $hostel)
                <option value="{{ $hostel->id }}" @if ($hostel->id == $category->hostel_id) selected @endif>

                    {{ $hostel->hostel_name }}

                </option>
            @endforeach


        </select>




        <input class="form-control mt-3" name="standard" value="{{ $category->standard }}">



        <input class="form-control mt-3" name="hostel_fee" value="{{ $category->hostel_fee }}">



        <textarea class="form-control mt-3" name="note">{{ $category->note }}</textarea>




        <button class="btn btn-primary mt-5">

            Update

        </button>



    </form>
@endsection

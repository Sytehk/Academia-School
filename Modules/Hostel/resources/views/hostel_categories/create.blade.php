@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Create Hostel Category') }}
@endsection



@section('content')
    <form action="{{ route('hostel-categories.store') }}" method="POST">

        @csrf


        <label>Hostel</label>

        <select name="hostel_id" class="form-control">

            <option value="">Global (applies to all hostels)</option>

            @foreach ($hostels as $hostel)
                <option value="{{ $hostel->id }}">
                    {{ $hostel->hostel_name }}
                </option>
            @endforeach

        </select>



        <label class="mt-3">
            Standard
        </label>

        <input type="text" name="standard" class="form-control">



        <label class="mt-3">
            Hostel Fee
        </label>


        <input type="number" name="hostel_fee" class="form-control">



        <label class="mt-3">
            Note
        </label>


        <textarea name="note" class="form-control"></textarea>



        <button class="btn btn-primary mt-5">

            Save

        </button>


    </form>
@endsection

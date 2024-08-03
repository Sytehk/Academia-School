@extends('layouts.backend')


@section('create-button')
    <a href="{{ route('hostel-members.create') }}" class="btn btn-primary btn-sm">

        Add Member

    </a>
@endsection



@section('content')
    <table class="table">


        <thead>

            <tr>

                <th>Student</th>
                <th>Class</th>
                <th>Hostel</th>
                <th>Category</th>
                <th>Fee</th>
                <th>Action</th>

            </tr>

        </thead>


        <tbody>


            @foreach ($members as $member)
                <tr>


                    <td>{{ $member->name }}</td>


                    <td>
                        {{ $member->class_name }}
                        -
                        {{ $member->section_name }}
                    </td>


                    <td>
                        {{ $member->hostel_name ?? 'Global' }}
                    </td>


                    <td>
                        {{ $member->standard }}
                    </td>


                    <td>
                        {{ $member->hostel_fee }}
                    </td>


                    <td>


                        <a href="{{ route('hostel-members.edit', $member->member_id) }}" class="btn btn-warning btn-sm">

                            Edit

                        </a>



                        <form action="{{ route('hostel-members.destroy', $member->member_id) }}" method="POST"
                            style="display:inline">

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


    {{ $members->links() }}
@endsection

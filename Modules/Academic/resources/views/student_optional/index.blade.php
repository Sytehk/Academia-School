@extends('layouts.backend')


@section('page_title_breadcrumb')
    Student Optional Subjects
@endsection



@section('create-button')
    <a href="{{ route('student-optional-subject.create') }}" class="btn btn-primary btn-sm">

        Add Student Subject

    </a>
@endsection




@section('content')
    <div class="card">


        <div class="card-body">

          <div class="table-responsive">
            <table class="table data-table">


                <thead>

                    <tr>

                        <th>Student</th>

                        <th>Subject</th>

                        <th>Config</th>

                        <th>Action</th>


                    </tr>


                </thead>


                <tbody>


                    @foreach ($data as $row)
                        <tr>
                            <td>
                                {{ $row->student->first_name }} {{ $row->student->last_name }}

                            </td>


                            <td>

                                {{ $row->subject->subject_name }}

                            </td>


                            <td>

                                {{ $row->config->name }}

                            </td>



                            <td>


                                <form action="{{ route('student-optional-subject.destroy', $row->id) }}" method="POST">

                                    <a href="{{ route('student-optional-subject.edit', $row->id) }}"
                                        class="btn btn-warning btn-sm">

                                        <i class="fa fa-pencil"></i>

                                    </a>


                                    @csrf
                                    @method('DELETE')


                                    <button class="btn btn-danger btn-sm show_confirm">

                                        <i class="fa fa-trash"></i>

                                    </button>


                                </form>



                            </td>



                        </tr>
                    @endforeach


                </tbody>


            </table>
          </div>

        </div>


    </div>
@endsection

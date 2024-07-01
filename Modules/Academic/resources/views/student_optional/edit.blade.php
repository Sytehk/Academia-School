@extends('layouts.backend')


@section('page_title_breadcrumb')
    Edit Student Optional Subject
@endsection



@section('content')
    <div class="card">


        <div class="card-body">


            <form method="POST" action="{{ route('student-optional-subject.update', $data->id) }}">


                @csrf

                @method('PUT')



                <div class="row">


                    <div class="col-lg-6">


                        <label class="my-2">

                            Student

                        </label>



                        <select name="student_id" class="form-select form-select-sm form-select-solid" data-control="select2">


                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @if ($data->student_id == $student->id) selected @endif>


                                    {{ $student->first_name }}
                                    {{ $student->last_name }}


                                </option>
                            @endforeach


                        </select>



                    </div>





                    <div class="col-lg-6">


                        <label class="my-2">

                            Student Session ID

                        </label>


                        <input type="number" name="student_session_id" class="form-control"
                            value="{{ $data->student_session_id }}" readonly>


                    </div>



                </div>







                <div class="row mt-4">



                    <div class="col-lg-6">


                        <label class="my-2">

                            Optional Subject Config

                        </label>



                        <select name="optional_subject_config_id" id="config_id"
                            class="form-select form-select-sm form-select-solid" data-control="select2">



                            @foreach ($configs as $config)
                                <option value="{{ $config->id }}" @if ($data->optional_subject_config_id == $config->id) selected @endif>


                                    {{ $config->name }}

                                    -

                                    {{ $config->class->class_name ?? '' }}


                                </option>
                            @endforeach



                        </select>


                    </div>







                    <div class="col-lg-6">


                        <label class="my-2">

                            Subject

                        </label>



                        <select name="subject_id" id="subject_id" class="form-select form-select-sm form-select-solid"
                            data-control="select2">


                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @if ($data->subject_id == $subject->id) selected @endif>


                                    {{ $subject->subject_name }}


                                </option>
                            @endforeach



                        </select>


                    </div>


                </div>







                <button class="btn btn-primary mt-5">

                    Update

                </button>



                <a href="{{ route('student-optional-subject.index') }}" class="btn btn-secondary mt-5">

                    Back

                </a>




            </form>



        </div>


    </div>
@endsection

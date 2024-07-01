@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Add Student Optional Subject') }}
@endsection



@section('create-button')
    <a href="{{ route('student-optional-subject.index') }}" class="btn btn-sm fw-bold btn-primary">

        {{ _lang('Student Optional List') }}

    </a>
@endsection





@section('content')
    <div class="row">


        <div class="col-lg-12">


            <div class="card card-default">


                <div class="card-header">

                    <div class="card-title">

                        {{ _lang('Assign Optional Subject To Student') }}

                    </div>

                </div>



                <div class="card-body">



                    <form method="POST" action="{{ route('student-optional-subject.store') }}">


                        @csrf




                        <div class="row">



                            <div class="col-lg-6">


                                <div class="form-group">


                                    <label class="my-2">

                                        {{ _lang('Student') }}

                                    </label>


                                    <select name="student_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2" required>



                                        <option value="">

                                            Select Student

                                        </option>


                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}">


                                                {{ $student->first_name }} {{ $student->last_name }}


                                            </option>
                                        @endforeach



                                    </select>



                                </div>

                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-lg-6">


                                <div class="form-group">


                                    <label class="my-2">

                                        {{ _lang('Optional Subject Config') }}

                                    </label>



                                    <select name="optional_subject_config_id" id="config_id"
                                        class="form-select form-select-sm form-select-solid" data-control="select2"
                                        required>



                                        <option value="">

                                            Select Config

                                        </option>




                                        @foreach ($configs as $config)
                                            <option value="{{ $config->id }}">


                                                {{ $config->name }}

                                                -

                                                {{ $config->class->class_name ?? '' }}


                                            </option>
                                        @endforeach



                                    </select>



                                </div>


                            </div>






                            <div class="col-lg-6">


                                <div class="form-group">


                                    <label class="my-2">

                                        {{ _lang('Subject') }}

                                    </label>



                                    <select name="subject_id" id="subject_id"
                                        class="form-select form-select-sm form-select-solid" data-control="select2"
                                        required>



                                        <option value="">

                                            Select Subject

                                        </option>



                                    </select>



                                </div>


                            </div>



                        </div>







                        <button type="submit" class="btn btn-primary mt-5">


                            {{ _lang('Save') }}


                        </button>




                    </form>



                </div>


            </div>


        </div>


    </div>
@endsection






@section('js-script')
    <script>
        $('#config_id').change(function() {



            let config_id = $(this).val();



            if (config_id) {



                $.ajax({


                    url: "{{ route('student-optional.getSubjects') }}",


                    type: "GET",



                    data: {


                        config_id: config_id


                    },



                    success: function(data) {



                        $('#subject_id').html(data);


                        $('#subject_id')
                            .trigger('change');



                    }


                });



            }



        });
    </script>
@endsection

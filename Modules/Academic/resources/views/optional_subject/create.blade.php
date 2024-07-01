@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Add Optional Subject') }}
@endsection



@section('create-button')
    <a href="{{ route('optional-subject-config.index') }}" class="btn btn-sm fw-bold btn-primary">

        {{ _lang('All Optional Subjects') }}

    </a>
@endsection






@section('content')
    <div class="row">


        <div class="col-lg-12">


            <div class="card card-default">


                <div class="card-header">

                    <div class="card-title">

                        {{ _lang('Create Optional Subject') }}

                    </div>

                </div>




                <div class="card-body">



                    <form action="{{ route('optional-subject-config.store') }}" method="POST">


                        @csrf





                        <div class="row">


                            <div class="col-lg-6">


                                <label class="my-2">

                                    {{ _lang('Class') }}

                                </label>



                                <select name="class_id" id="class_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" required>


                                    <option value="">

                                        Select Class

                                    </option>


                                    {!! create_option('classes', 'id', 'class_name', old('class_id')) !!}


                                </select>


                            </div>






                            <div class="col-lg-6">


                                <label class="my-2">

                                    {{ _lang('Group') }}

                                </label>



                                <select name="group_id" id="group_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2">


                                    <option value="">

                                        All Group

                                    </option>


                                    {!! create_option('student_groups', 'id', 'group_name', old('group_id')) !!}



                                </select>



                            </div>


                        </div>





                        <div class="row mt-3">


                            <div class="col-lg-6">


                                <label>

                                    {{ _lang('Config Name') }}

                                </label>



                                <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                    placeholder="4th Subject">


                            </div>




                            <div class="col-lg-3">


                                <label>

                                    {{ _lang('Total Mark') }}

                                </label>


                                <input type="number" name="total_mark" value="100" class="form-control">


                            </div>





                            <div class="col-lg-3">


                                <label>

                                    {{ _lang('Selection Limit') }}

                                </label>


                                <input type="number" name="selection_limit" value="1" class="form-control">


                            </div>


                        </div>







                        <div class="mt-5">


                            <label>

                                {{ _lang('Select Subjects') }}

                            </label>



                            <select name="subjects[]" id="subjects" multiple class="form-control form-select"
                                data-control="select2" required>


                            </select>


                        </div>






                        <button class="btn btn-primary mt-5">


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
        $('#class_id,#group_id').change(function() {


            let class_id =
                $('#class_id').val();


            let group_id =
                $('#group_id').val();



            if (class_id) {


                $.ajax({

                    url: "{{ route('optional-subject.getSubjects') }}",


                    data: {


                        class_id: class_id,

                        group_id: group_id


                    },


                    success: function(response) {


                        $('#subjects').html(response);


                        $('#subjects').trigger('change');


                    }



                });


            }



        });
    </script>
@endsection

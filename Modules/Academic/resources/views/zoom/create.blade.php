@extends('layouts.backend')


@section('content')
    <form method="POST" action="{{ route('zoom.store') }}">

        @csrf


        <div class="row my-3">


            {{-- CLASS --}}
            <div class="col-lg-6">

                <div class="form-group">

                    <label class="my-2 control-label">
                        {{ _lang('Class') }}
                    </label>


                    <select name="class_id" class="form-select form-select-sm form-select-solid" data-control="select2"
                        onchange="getData(this.value)" required>


                        <option value="">
                            {{ _lang('Select Class') }}
                        </option>


                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">

                                {{ $class->class_name }}

                            </option>
                        @endforeach


                    </select>


                </div>


            </div>




            {{-- SECTION --}}
            <div class="col-lg-6">


                <div class="form-group">


                    <label class="my-2 control-label">

                        {{ _lang('Section') }}

                    </label>



                    <select name="section_id" class="form-select form-select-sm form-select-solid" data-control="select2"
                        id="section_id" required>


                        <option value="">

                            {{ _lang('Select Section') }}

                        </option>


                    </select>


                </div>


            </div>



        </div>





        <div class="form-group my-3">


            <label>
                {{ _lang('Meeting Topic') }}
            </label>


            <input type="text" name="topic" class="form-control" placeholder="Enter meeting topic" required>


        </div>





        <div class="form-group my-3">


            <label>
                {{ _lang('Agenda') }}
            </label>


            <textarea name="agenda" class="form-control" placeholder="Meeting agenda"></textarea>


        </div>





        <div class="form-group my-3">


            <label>
                {{ _lang('Start Time') }}
            </label>


            <input type="datetime-local" name="start_time" class="form-control" required>


        </div>





        <div class="row">


            <div class="col-lg-6">


                <label>
                    {{ _lang('Duration Minutes') }}
                </label>


                <input type="number" name="duration" class="form-control" placeholder="60" required>


            </div>




            <div class="col-lg-6">


                <label>
                    {{ _lang('Password') }}
                </label>


                <input type="text" name="password" class="form-control" placeholder="Optional">


            </div>



        </div>





        <button class="btn btn-primary mt-5">

            {{ _lang('Create Meeting') }}

        </button>



    </form>
@endsection

<script>
    function getData(class_id) {


        let token =
            $('input[name=_token]').val();



        if (!class_id) {


            $('#section_id').html(
                '<option value="">Select Section</option>'
            );


            return;

        }



        $.ajax({


            type: "POST",


            url: "{{ url('sections-section') }}",


            data: {


                _token: token,

                class_id: class_id

            },


            beforeSend: function() {


                $("#preloader")
                    .css("display", "block");


            },


            success: function(response) {


                $("#preloader")
                    .css("display", "none");



                $("#section_id")
                    .html(response)
                    .trigger('change');


            },



            error: function() {


                $("#preloader")
                    .css("display", "none");



                $("#section_id")
                    .html(

                        '<option value="">No Section Found</option>'

                    );


            }


        });



    }
</script>

@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Update Hostel Member') }}
@endsection



@section('content')
    <form action="{{ route('hostel-members.update', $member->id) }}" method="POST">


        @csrf

        @method('PUT')




        <div class="form-group">


            <label>
                Student
            </label>


            <select name="student_id" class="form-control">


                <option value="">
                    Select Student
                </option>



                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @if ($student->id == $member->student_id) selected @endif>

                        {{ $student->name }}

                    </option>
                @endforeach


            </select>


        </div>





        <div class="form-group mt-4">


            <label>
                Hostel Category
            </label>


            <select name="hostel_category_id" class="form-control" id="hostel_category_id">


                <option value="">
                    Select Category
                </option>



                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" data-fee="{{ $category->hostel_fee }}"
                        @if ($category->id == $member->hostel_category_id) selected @endif>


                        {{ $category->hostel?->hostel_name ?? 'Global' }}
                        -
                        {{ $category->standard }}


                    </option>
                @endforeach


            </select>



        </div>





        <div class="form-group mt-4">


            <label>
                Hostel Fee
            </label>


            <input type="text" id="hostel_fee" class="form-control" readonly
                value="{{ $member->category->hostel_fee ?? '' }}">



        </div>





        <button class="btn btn-primary mt-5">

            Update Member

        </button>



    </form>





    <script>
        document
            .getElementById('hostel_category_id')
            .addEventListener('change', function() {


                let fee = this.options[this.selectedIndex]
                    .getAttribute('data-fee');


                document.getElementById('hostel_fee').value = fee;


            });
    </script>
@endsection

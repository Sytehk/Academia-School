@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ ucfirst('behavior') }}
@endsection

@section('create-button')
    <a href="{{ route('behaviors.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ ucfirst('behavior') }} List
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('behaviors.store') }}" enctype="multipart/form-data" method="post">
                @csrf

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>{{ _lang('Title') }}</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>{{ _lang('Date') }}</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>


                    <div class="col-md-6 mb-3">
                        <label>{{ _lang('Class') }}</label>
                        <select name="class_id" class="form-select" onchange="getSections(this.value)"
                            data-control="select2" required>
                            {!! create_option('classes', 'id', 'class_name') !!}
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>{{ _lang('Section') }}</label>
                        <select name="section_id" class="form-select" onchange="getStudents(this.value)"
                            data-control="select2" required>
                            <option value="">Select Section</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>{{ _lang('Student') }}</label>
                        <select name="student_id" class="form-select" id="student_id" data-control="select2" required>
                            <option value="">{{ _lang('Select Student') }}</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>{{ _lang('Note') }}</label>
                        <textarea name="note" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>{{ _lang('Attachment') }}</label>
                        <input type="file" name="file" class="form-control">
                    </div>

                </div>

                <div class="form-group">
                    <div class="col-sm-offset-4 col-sm-9">
                        <button type="submit" class="btn btn-primary mt-5">{{ _lang('Create') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        function getSections(classId) {
            let _token = $('input[name=_token]').val();

            $.post("{{ url('sections-section') }}", {
                _token: _token,
                class_id: classId
            }, function(response) {

                let sectionSelect = $('select[name=section_id]');

                sectionSelect.html(response);

                // reset student
                $('#student_id').html('<option value="">Select Student</option>');

                // IMPORTANT: reset select2 value + trigger change
                sectionSelect.val('').trigger('change');
            });
        }

        function getStudents() {

            let _token = $('input[name=_token]').val();
            let class_id = $('select[name=class_id]').val();
            let section_id = $('select[name=section_id]').val();

            if (!class_id || !section_id) {
                $('#student_id').html('<option value="">Select Student</option>');
                return;
            }

            $.post("{{ route('behaviors.students') }}", {
                _token: _token,
                class_id: class_id,
                section_id: section_id
            }, function(response) {
                $('#student_id').html(response);
            });
        }
    </script>
@endsection

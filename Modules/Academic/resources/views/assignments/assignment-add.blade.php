@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Assignment') }}
@endsection

@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('assignments.index') }}">
        {{ _lang('Assignment List') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('assignments.store') }}" enctype="multipart/form-data" method="post">
                @csrf
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Title') }}</label>
                    <input type="text" class="form-control" name="title" value="{{ old('title') }}" placeholder="Title"
                        required>
                </div>
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Description') }}</label>
                    <textarea class="form-control" id="summernote" name="description" placeholder="Description">{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Deadline') }}</label>
                    <input type="date" class="form-control" name="deadline" value="{{ old('deadline') }}" required>
                </div>
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Class') }}</label>
                    <select class="form-select form-select-sm form-select-solid" data-control="select2" name="class_id"
                        onChange="getData(this.value);" required>
                        {!! create_option('classes', 'id', 'class_name', old('class_id')) !!}
                    </select>
                </div>
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Section') }}</label>

                    <select name="section_id" class="form-select form-select-sm form-select-solid" data-control="select2"
                        required>

                    </select>
                </div>
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Subject') }}</label>

                    <select name="subject_id" class="form-select form-select-sm form-select-solid" data-control="select2"
                        required>

                        {!! create_option('subjects', 'id', 'subject_name', null) !!}
                    </select>
                </div>
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('File') }}</label>

                    <input type="file" class="form-control" name="file" required>
                </div>
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('File (Optional)') }}</label>

                    <input type="file" class="form-control" name="file_2">
                </div>
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('File (Optional)') }}</label>

                    <input type="file" class="form-control" name="file_3">
                </div>
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('File (Optional)') }}</label>

                    <input type="file" class="form-control" name="file_4">
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-5">
                        <button type="submit" class="mt-5 btn btn-primary">Add Assignment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@section('js-script')
    <script type="text/javascript">
        function getData(val) {
            var _token = $('input[name=_token]').val();
            var class_id = $('select[name=class_id]').val();
            $.ajax({
                type: "POST",
                url: "{{ url('sections-section') }}",
                data: {
                    _token: _token,
                    class_id: class_id
                },
                success: function(sections) {
                    $('select[name=section_id]').html(sections);
                }
            });
        }
    </script>
@stop
@endsection

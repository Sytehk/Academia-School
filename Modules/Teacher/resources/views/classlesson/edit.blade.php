@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ ucfirst('classlesson') }}
@endsection

@section('create-button')
    <a href="{{ route('classlessons.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ ucfirst('classlesson') }} List
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Update Class Lessons') }}
                    </div>
                </div>
                <div class="">
                    <div class="col-lg-10">
                        <form action="{{ route('classlessons.update', $classlesson->id) }}" enctype="multipart/form-data"
                            method="post">
                            @csrf
                            @method('PATCH')

                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Title') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="title"
                                        value="{{ $classlesson->title }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Description') }}</label>
                                <div class="col-sm-9">
                                    <textarea id="summernote" class="form-control" name="note">{{ $classlesson->note }}</textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Date') }}</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" name="date"
                                        value="{{ $classlesson->date }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Class') }}</label>
                                <div class="col-sm-6">
                                    <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                        name="class_id" onChange="getData(this.value);" required>

                                        {!! create_option('classes', 'id', 'class_name', $classlesson->class_id) !!}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Section') }}</label>
                                <div class="col-sm-6">
                                    <select name="section_id" onChange="getSubject();"
                                        class="form-select form-select-sm form-select-solid" data-control="select2"
                                        data-selected="{{ old('section_id', $classlesson->section_id) }}" required>

                                        <option value="">{{ _lang('Select Class First') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Subject') }}</label>
                                <div class="col-sm-6">
                                    <select name="subject_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2">

                                        {!! create_option('subjects', 'id', 'subject_name', $classlesson->subject_id) !!}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('File') }}</label>
                                <div class="col-sm-6">
                                    <input type="file" class="form-control appsvan-file"
                                        data-value="{{ $classlesson->file }}" name="file">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('File (Optional)') }}</label>
                                <div class="col-sm-6">
                                    <input type="file" class="form-control appsvan-file"
                                        data-value="{{ $classlesson->file_2 }}" name="file_2">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('File (Optional)') }}</label>
                                <div class="col-sm-6">
                                    <input type="file" class="form-control appsvan-file"
                                        data-value="{{ $classlesson->file_3 }}" name="file_3">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('File (Optional)') }}</label>
                                <div class="col-sm-6">
                                    <input type="file" class="form-control appsvan-file"
                                        data-value="{{ $classlesson->file_4 }}" name="file_4">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-5">
                                    <button type="submit"
                                        class="btn btn-primary mt-5">{{ _lang('Update Class Lessons') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
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
@endsection

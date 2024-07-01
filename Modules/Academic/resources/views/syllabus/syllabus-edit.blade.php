@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Syllabus') }}
@endsection

@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('syllabus.index') }}">
        {{ _lang('Syllabus List') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('syllabus.update', $syllabus->id) }}" enctype="multipart/form-data" method="post">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Class') }}</label>
                    <select class="form-select form-select-sm form-select-solid" data-control="select2" name="class_id"
                        required>
                        {!! create_option('classes', 'id', 'class_name', $syllabus->class_id) !!}
                    </select>
                </div>

                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Title') }}</label>
                    <input type="text" class="form-control" name="title" value="{{ $syllabus->title }}"
                        placeholder="Title" required>
                </div>
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Description') }}</label>
                    <textarea id="summernote" class="form-control" name="description" cols="4" rows="6"
                        placeholder="Description">{{ $syllabus->description }}</textarea>
                </div>


                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('File') }}</label>
                    <input type="file" class="form-control appsvan-file" name="file"
                        data-value="{{ $syllabus->file }}" required>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-5">
                        <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update Syllabus') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

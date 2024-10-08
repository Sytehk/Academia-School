@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Assignment Submit List') }}
@endsection

@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ url('student/assignments-submit') }}">
        {{ _lang('Assignment Submit List') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ url('student/assignments-submit', $assignmentSubmit->id) }}" autocomplete="off"
                enctype="multipart/form-data" method="post">
                @csrf
                @method('PATCH')

                <input type="hidden" name="assignment_id" value="{{ $assignmentSubmit->assignment_id }}">

                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('File') }}</label>
                    <input type="file" class="form-control" data-value="{{ $assignmentSubmit->file }}" name="file">
                </div>
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('File (Optional)') }}</label>
                    <input type="file" class="form-control" data-value="{{ $assignmentSubmit->file_2 }}" name="file_2">
                </div>
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('File (Optional)') }}</label>
                    <input type="file" class="form-control" data-value="{{ $assignmentSubmit->file_3 }}" name="file_3">
                </div>
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('File (Optional)') }}</label>
                    <input type="file" class="form-control" data-value="{{ $assignmentSubmit->file_4 }}" name="file_4">
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-5">
                        <button type="submit"
                            class="mt-5 btn btn-primary">{{ _lang('Update Assignment Submit') }}</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

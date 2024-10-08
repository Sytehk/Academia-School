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
        <div class="col-sm-12">
            <table class="table table-striped table-bordered" width="100%">
                <tbody>
                    <tr>
                        <td>{{ _lang('Title') }}</td>
                        <td colspan="5"><b>{{ $assignment->title }}</b></td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Class') }}</td>
                        <td><b>{{ $assignment->class_name }}</b></td>
                        <td>{{ _lang('Section') }}</td>
                        <td><b>{{ $assignment->section_name }}</b></td>
                        <td>{{ _lang('Subject') }}</td>
                        <td><b>{{ $assignment->subject_name }}</b></td>
                    </tr>
                    <tr>
                        <td colspan="6" class="details-td">{!! $assignment->description !!}</td>
                    </tr>
                    <tr>
                        <td>{{ _lang('Assignment Files') }}</td>
                        <td colspan="5">
                            @if ($assignment->file)
                                <a class="btn btn-primary rect-btn" target="_blank"
                                    href="{{ asset('uploads/files/assignments/' . $assignment->file) }}">{{ _lang('Download File 1') }}</a>
                            @endif
                            @if ($assignment->file_2)
                                <a class="btn btn-primary rect-btn" target="_blank"
                                    href="{{ asset('uploads/files/assignments/' . $assignment->file_2) }}">{{ _lang('Download File 2') }}</a>
                            @endif
                            @if ($assignment->file_3)
                                <a class="btn btn-primary rect-btn" target="_blank"
                                    href="{{ asset('uploads/files/assignments/' . $assignment->file_3) }}">{{ _lang('Download File 3') }}</a>
                            @endif
                            @if ($assignment->file_4)
                                <a class="btn btn-primary rect-btn" target="_blank"
                                    href="{{ asset('uploads/files/assignments/' . $assignment->file_4) }}">{{ _lang('Download File 4') }}</a>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ url('student/assignments-submit') }}" enctype="multipart/form-data" method="post">
                @csrf

                <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">

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
                        <button type="submit" class="mt-5 btn btn-primary">Submit Assignment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

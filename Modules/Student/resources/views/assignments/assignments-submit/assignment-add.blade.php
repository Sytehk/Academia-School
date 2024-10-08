@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Assignment') }}
@endsection

@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ url('student/assignments-submit') }}">
        {{ _lang('Assignment List') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ url('student/assignments-submit') }}" enctype="multipart/form-data" method="post">
                @csrf

                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Assignment') }}</label>

                    <select name="assignment_id" class="form-select form-select-sm form-select-solid" data-control="select2"
                        required>
                        <option value=""> -- Selected Assignment --</option>
                        @foreach ($assignments as $assignment)
                            <option value="{{ $assignment->id }}">{{ $assignment->title }} - {{ $assignment->deadline }}
                            </option>
                        @endforeach
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
                        <button type="submit" class="mt-5 btn btn-primary">Submit Assignment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

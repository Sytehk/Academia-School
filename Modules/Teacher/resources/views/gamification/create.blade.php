@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Gamification') }}
@endsection

@section('content')
    <form action="{{ route('gamifications.store') }}" method="POST" enctype="multipart/form-data">

        @csrf

        <div class="card">
            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>{{ _lang('Title') }}</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>{{ _lang('Date') }}</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date') }}" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>{{ _lang('Class') }}</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">
                                Select Class
                            </option>

                            {!! create_option('classes', 'id', 'class_name') !!}
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>{{ _lang('Section') }}</label>
                        <select name="section_id" class="form-select">
                            <option value="">{{ _lang('Select Class First') }}</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>{{ _lang('Student') }}</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">
                                Select Student
                            </option>

                            {!! create_option('students', 'id', 'first_name') !!}
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>{{ _lang('Note') }}</label>

                        <textarea name="note" rows="4" class="form-control">{{ old('note') }}</textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>{{ _lang('Attachment') }}</label>

                        <input type="file" name="file" class="form-control">
                    </div>

                </div>

            </div>

            <div class="card-footer">
                <button class="btn btn-primary">
                    Save
                </button>
            </div>
        </div>

    </form>
@endsection

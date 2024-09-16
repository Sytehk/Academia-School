@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ ucfirst('gamification') }}
@endsection

@section('create-button')
    <a href="{{ route('gamifications.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ ucfirst('gamification') }} List
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('gamifications.update', $gamification->id) }}" enctype="multipart/form-data" method="post">
                @csrf
                @method('PATCH')

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

                            {!! create_option('classes', 'id', 'class_name', old('class_id', $gamification->class_id)) !!}
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>{{ _lang('Section') }}</label>
                        <select name="section_id" class="form-select"
                            data-selected="{{ old('section_id', $gamification->section_id ?? '') }}">
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

                <div class="form-group">
                    <div class="col-sm-offset-4 col-sm-9">
                        <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

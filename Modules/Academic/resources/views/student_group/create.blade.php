@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Student Group') }}
@endsection
@section('create-button')
    <a href="{{ route('student-groups.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Student Groups') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form method="post" action="{{ route('student-groups.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="control-label my-2">{{ _lang('Group Name') }}</label>
                        <input type="text" class="form-control" name="group_name" placeholder="Group Name"
                            value="{{ old('group_name') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-lg-12">
                        <button type="reset" class="btn btn-danger mt-5">{{ _lang('Reset') }}</button>
                        <button type="submit" class="btn btn-primary mt-5">{{ _lang('Save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

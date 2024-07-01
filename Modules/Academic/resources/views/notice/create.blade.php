@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Notice') }}
@endsection
@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('notices.index') }}">
        {{ _lang('Notice List') }}
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <form method="post" action="{{ route('notices.store') }}" enctype="multipart/form-data" data-disable-on-submit>
                @csrf

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Title') }}</label>
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Notice') }}</label>
                            <textarea class="form-control summernote" name="notice" required>{{ old('notice') }}</textarea>
                            @error('notice')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('User Type') }}</label>
                            <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                name="user_type[]" multiple>
                                @foreach ($roles as $role)
                                    @if (!in_array(strtolower($role->name), ['superadmin', 'owner']))
                                        <option value="{{ $role->name }}">
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>

                            @error('user_type')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror

                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Image') }}</label>
                            <input type="file" class="form-control" name="image">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mt-5">
                        <div class="form-group">
                            <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
                            <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

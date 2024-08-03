@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Teacher') }}
@endsection

@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('teachers.bulk-file-upload') }}">
        {{ _lang('CSV/Xlsx Upload File') }}
    </a>
@endsection

@section('content')
    <form action="{{ route('teachers.store') }}" method="post"enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Name') }} <span class='text-danger'>*</span> </label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}"
                        placeholder="Name" required>

                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group my-5">
            <button type="submit" class="btn btn-primary mt-5">{{ _lang('Create Teacher') }}</button>
        </div>
    </form>
@endsection

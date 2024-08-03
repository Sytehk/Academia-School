@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Teacher') }}
@endsection

@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('teachers.bulk-file-upload') }}">
        {{ _lang('Bulk Teacher Upload') }}
    </a>
@endsection

@section('content')
    <form action="{{ route('teachers.update', $teacher->id) }}"method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="form-group">
                    <label class="control-label my-2">{{ _lang('Name') }}</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $teacher->name }}"
                        placeholder="Name">

                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group my-5">
            <button type="submit" class="btn btn-primary mt-5">Update Teacher</button>
        </div>
    </form>
@endsection

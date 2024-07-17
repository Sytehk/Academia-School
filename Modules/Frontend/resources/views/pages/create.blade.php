@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Create Page') }}
@endsection



@section('create-button')
    <a href="{{ route('pages.index') }}" class="btn btn-sm btn-primary fw-bold">

        {{ _lang('All Pages') }}

    </a>
@endsection




@section('content')
    <div class="row">

        <div class="col-lg-12">


            <form action="{{ route('pages.store') }}" method="POST">

                @csrf



                <div class="form-group">

                    <label class="my-2">
                        Title <span class="text-danger">*</span>
                    </label>


                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title') }}" required>


                    @error('title')
                        <span class="text-danger">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="my-2">
                        Slug
                    </label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug') }}">
                </div>

                <div class="form-group">
                    <label class="my-2">
                        Content
                    </label>

                    <textarea name="content" rows="6" class="form-control" id="editor">{{ old('content') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="my-2">
                        Status
                    </label>

                    <select name="status" class="form-control">
                        <option value="1">
                            Active
                        </option>

                        <option value="0">
                            Inactive
                        </option>
                    </select>
                </div>

                <button class="btn btn-primary mt-4">
                    Save Page
                </button>
            </form>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/ckeditor/ckeditor.js') }}"></script>


    <script>
        ClassicEditor
            .create(document.querySelector('#editor'))
            .catch(error => {

                console.error(error);

            });
    </script>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create About Us') }}
@endsection

@section('create-button')
    <a href="{{ route('about-us.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All About Us') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('about-us.store') }}" enctype="multipart/form-data">

                @csrf

                <div class="form-group">
                    <label class="my-2">{{ _lang('Title') }}</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>

                <div class="form-group">
                    <label class="my-2">{{ _lang('Short Description') }}</label>
                    <textarea name="description" class="form-control" rows="8">{{ old('description') }}</textarea>
                    <small class="text-muted">{{ _lang('Shown on the homepage and footer preview.') }}</small>
                </div>

                <div class="form-group">
                    <label class="my-2">{{ _lang('Long Description') }}</label>
                    <textarea name="long_description" id="editor" class="form-control" rows="12">{{ old('long_description') }}</textarea>
                    <small class="text-muted">{{ _lang('Shown in full on the About Us page.') }}</small>
                </div>

                <div class="form-group">
                    <label class="my-2">{{ _lang('Image') }}</label>
                    <input type="file" name="image" class="form-control">
                </div>

                <button class="btn btn-primary mt-4">
                    {{ _lang('Save About Us') }}
                </button>

            </form>

        </div>
    </div>

    <script src="{{ asset('assets/vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
        ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));
    </script>
@endsection

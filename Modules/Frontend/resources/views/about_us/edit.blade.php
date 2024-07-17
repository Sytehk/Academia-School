@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit About Us') }}
@endsection

@section('create-button')
    <a href="{{ route('about-us.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All About Us') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('about-us.update', $id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label class="my-2">{{ _lang('Title') }}</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $aboutUs->title) }}" required>
                </div>

                <div class="form-group">
                    <label class="my-2">{{ _lang('Short Description') }}</label>
                    <textarea name="description" class="form-control" rows="8">{{ old('description', $aboutUs->description) }}</textarea>
                    <small class="text-muted">{{ _lang('Shown on the homepage and footer preview.') }}</small>
                </div>

                <div class="form-group">
                    <label class="my-2">{{ _lang('Long Description') }}</label>
                    <textarea name="long_description" id="editor" class="form-control" rows="12">{{ old('long_description', $aboutUs->long_description) }}</textarea>
                    <small class="text-muted">{{ _lang('Shown in full on the About Us page.') }}</small>
                </div>

                <div class="form-group">
                    <label class="my-2">{{ _lang('Image') }}</label>
                    <input type="file" name="image" class="form-control">

                    @if ($aboutUs->image)
                        <img src="{{ asset('storage/about_us/' . $aboutUs->image) }}" width="120" class="mb-2 rounded">
                    @endif
                </div>

                <button class="btn btn-primary mt-4">
                    {{ _lang('Update About Us') }}
                </button>

            </form>

        </div>
    </div>

    <script src="{{ asset('assets/vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
        ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));
    </script>
@endsection

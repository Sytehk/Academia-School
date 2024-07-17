@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Edit Page') }}
@endsection

@section('create-button')
    <a href="{{ route('pages.index') }}" class="btn btn-sm btn-primary fw-bold">

        {{ _lang('All Pages') }}

    </a>
@endsection

@section('content')
    <div class="row">

        <div class="col-lg-12">


            <form action="{{ route('pages.update', $page->id) }}" method="POST">


                @csrf

                @method('PUT')





                <div class="form-group">


                    <label class="my-2">
                        Title <span class="text-danger">*</span>
                    </label>


                    <input type="text" name="title" class="form-control" value="{{ old('title', $page->title) }}"
                        required>


                </div>

                <div class="form-group">
                    <label class="my-2">
                        Slug
                    </label>

                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $page->slug) }}">
                </div>

                <div class="form-group">
                    <label class="my-2">
                        Content
                    </label>
                    <textarea name="content" rows="6" class="form-control" id="editor">{{ old('content', $page->content) }}</textarea>

                </div>

                <div class="form-group">
                    <label class="my-2">
                        Status
                    </label>

                    <select name="status" class="form-control">


                        <option value="1" {{ $page->status == 1 ? 'selected' : '' }}>

                            Active

                        </option>



                        <option value="0" {{ $page->status == 0 ? 'selected' : '' }}>

                            Inactive

                        </option>


                    </select>


                </div>







                <button class="btn btn-primary mt-4">

                    Update Page

                </button>



            </form>


        </div>

    </div>




    <script src="{{ asset('assets/vendor/ckeditor/ckeditor.js') }}"></script>


    <script>
        ClassicEditor
            .create(document.querySelector('#editor'))
            .catch(error => console.log(error));
    </script>
@endsection

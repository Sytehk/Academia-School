@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Testimonial') }}
@endsection

@section('create-button')
    <a href="{{ route('testimonials.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Testimonials') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('testimonials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group mb-3">
                    <label>{{ _lang('Name') }}</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Description') }}</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Rating (0-5)') }}</label>
                    <input type="number" name="ratting" class="form-control" min="0" max="5" value="0">
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Video URL') }}</label>
                    <input type="text" name="video_url" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Note') }}</label>
                    <input type="text" name="note" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Thumbnail Image') }}</label>
                    <input type="file" name="thumbnail_image" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Status') }}</label>
                    <select name="status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <button class="btn btn-primary mt-3">
                    {{ _lang('Save') }}
                </button>

            </form>

        </div>
    </div>
@endsection

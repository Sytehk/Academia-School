@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Testimonial') }}
@endsection

@section('create-button')
    <a href="{{ route('testimonials.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Testimonials') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('testimonials.update', $id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="form-group mb-3">
                    <label>{{ _lang('Name') }}</label>
                    <input type="text" name="name" class="form-control" value="{{ $testimonial->name }}" required>
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Description') }}</label>
                    <textarea name="description" class="form-control">{{ $testimonial->description }}</textarea>
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Rating') }}</label>
                    <input type="number" name="ratting" class="form-control" value="{{ $testimonial->ratting }}"
                        min="0" max="5">
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Video URL') }}</label>
                    <input type="text" name="video_url" value="{{ $testimonial->video_url }}" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Note') }}</label>
                    <input type="text" name="note" value="{{ $testimonial->note }}" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Thumbnail Image') }}</label>

                    @if ($testimonial->thumbnail_image)
                        <br>
                        <img src="{{ asset('storage/testimonials/' . $testimonial->thumbnail_image) }}" width="80"
                            class="mb-2">
                    @endif

                    <input type="file" name="thumbnail_image" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Status') }}</label>
                    <select name="status" class="form-control">
                        <option value="1" {{ $testimonial->status == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $testimonial->status == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <button class="btn btn-primary mt-3">
                    {{ _lang('Update') }}
                </button>

            </form>

        </div>
    </div>
@endsection

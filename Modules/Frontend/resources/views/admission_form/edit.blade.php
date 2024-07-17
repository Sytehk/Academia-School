@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Academic Image') }}
@endsection

@section('create-button')
    <a href="{{ route('gallery-images.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Images') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('gallery-images.update', $id) }}" enctype="multipart/form-data">

                @csrf
                @method('PATCH')

                <div class="row">

                    <div class="col-md-4">
                        <label>Title</label>
                        <input type="text" name="title" value="{{ $academicImage->title }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>Heading</label>
                        <input type="text" name="heading" value="{{ $academicImage->heading }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ $academicImage->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $academicImage->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="form-group mt-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ $academicImage->description }}</textarea>
                </div>

                <div class="form-group mt-3">
                    <label>Image</label>

                    <br>

                    @if ($academicImage->image)
                        <img src="{{ asset('storage/academic_images/' . $academicImage->image) }}" width="120"
                            class="mb-2">
                    @endif

                    <input type="file" name="image" class="form-control">
                </div>

                <button class="btn btn-primary mt-4">
                    Update Image
                </button>

            </form>

        </div>
    </div>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Banner') }}
@endsection

@section('create-button')
    <a href="{{ route('banners.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Banners') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('banners.store') }}" enctype="multipart/form-data">

                @csrf

                <div class="form-group">
                    <label class="my-2">
                        Title <span class="text-danger">*</span>
                    </label>

                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title') }}" maxlength="191" required>

                    @error('title')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>


                <div class="form-group">
                    <label class="my-2">
                        Description
                    </label>

                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" maxlength="255">{{ old('description') }}</textarea>

                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>


                <div class="form-group">
                    <label class="my-2">
                        Button Name
                    </label>

                    <input type="text" name="button_name" class="form-control @error('button_name') is-invalid @enderror"
                        value="{{ old('button_name') }}" maxlength="20">

                    @error('button_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>


                <div class="form-group">
                    <label class="my-2">
                        Button Link
                    </label>

                    <input type="text" name="button_link" class="form-control @error('button_link') is-invalid @enderror"
                        value="{{ old('button_link') }}" maxlength="100">

                    @error('button_link')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>


                <div class="form-group">
                    <label class="my-2">
                        Banner Image <span class="text-danger">*</span>
                    </label>

                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                        accept="image/*" required>

                    @error('image')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>


                <button type="submit" class="btn btn-primary mt-4">
                    Save Banner
                </button>

            </form>

        </div>
    </div>
@endsection

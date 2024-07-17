@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Mobile App Section') }}
@endsection

@section('create-button')
    <a href="{{ route('mobile-app-sections.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All List') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('mobile-app-sections.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Heading</label>
                    <input type="text" name="heading" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image" class="form-control">
                </div>

                <div class="form-group">
                    <label>Feature One</label>
                    <input type="text" name="feature_one" class="form-control">
                </div>

                <div class="form-group">
                    <label>Feature Two</label>
                    <input type="text" name="feature_two" class="form-control">
                </div>

                <div class="form-group">
                    <label>Feature Three</label>
                    <input type="text" name="feature_three" class="form-control">
                </div>

                <div class="form-group">
                    <label>Play Store Link</label>
                    <input type="text" name="play_store_link" class="form-control">
                </div>

                <div class="form-group">
                    <label>App Store Link</label>
                    <input type="text" name="app_store_link" class="form-control">
                </div>

                <button class="btn btn-primary mt-3">Save</button>

            </form>

        </div>
    </div>
@endsection

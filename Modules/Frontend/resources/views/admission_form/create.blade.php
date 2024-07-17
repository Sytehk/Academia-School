@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Academic Image') }}
@endsection

@section('create-button')
    <a href="{{ route('gallery-images.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Images') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('gallery-images.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row">

                    <div class="col-md-4">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>Heading</label>
                        <input type="text" name="heading" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="form-group mt-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>

                <div class="form-group mt-3">
                    <label>Image</label>
                    <input type="file" name="image" class="form-control">
                </div>

                <button class="btn btn-primary mt-4">
                    Save Image
                </button>

            </form>

        </div>
    </div>
@endsection

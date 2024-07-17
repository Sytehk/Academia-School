@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Institute Image Setting') }}
@endsection

@section('create-button')
    <a href="{{ route('institute-image-settings.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Settings') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('institute-image-settings.store') }}" enctype="multipart/form-data">

                @csrf

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Header Logo Light Theme</label>
                        <input type="file" name="header_logo_light_theme" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Header Logo Dark Theme</label>
                        <input type="file" name="header_logo_dark_theme" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Footer Logo Light Theme</label>
                        <input type="file" name="footer_logo_light_theme" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Footer Logo Dark Theme</label>
                        <input type="file" name="footer_logo_dark_theme" class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Favicon</label>
                        <input type="file" name="favicon" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Status</label>

                        <select name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                </div>

                <button class="btn btn-primary mt-3">
                    Save
                </button>

            </form>

        </div>
    </div>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Institute Image Setting') }}
@endsection

@section('create-button')
    <a href="{{ route('institute-image-settings.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Settings') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('institute-image-settings.update', $id) }}" enctype="multipart/form-data">

                @csrf
                @method('PATCH')

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Header Logo Light Theme</label>

                        <br>

                        @if ($instituteImage->header_logo_light_theme)
                            <img src="{{ asset('storage/institute_image_settings/' . $instituteImage->header_logo_light_theme) }}"
                                width="100" class="mb-2">
                        @endif

                        <input type="file" name="header_logo_light_theme" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Header Logo Dark Theme</label>

                        <br>

                        @if ($instituteImage->header_logo_dark_theme)
                            <img src="{{ asset('storage/institute_image_settings/' . $instituteImage->header_logo_dark_theme) }}"
                                width="100" class="mb-2">
                        @endif

                        <input type="file" name="header_logo_dark_theme" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Footer Logo Light Theme</label>

                        <br>

                        @if ($instituteImage->footer_logo_light_theme)
                            <img src="{{ asset('storage/institute_image_settings/' . $instituteImage->footer_logo_light_theme) }}"
                                width="100" class="mb-2">
                        @endif

                        <input type="file" name="footer_logo_light_theme" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Footer Logo Dark Theme</label>

                        <br>

                        @if ($instituteImage->footer_logo_dark_theme)
                            <img src="{{ asset('storage/institute_image_settings/' . $instituteImage->footer_logo_dark_theme) }}"
                                width="100" class="mb-2">
                        @endif

                        <input type="file" name="footer_logo_dark_theme" class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Favicon</label>

                        <br>

                        @if ($instituteImage->favicon)
                            <img src="{{ asset('storage/institute_image_settings/' . $instituteImage->favicon) }}"
                                width="150" class="mb-2">
                        @endif

                        <input type="file" name="favicon" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">

                        <label>Status</label>

                        <select name="status" class="form-control">

                            <option value="1" {{ $instituteImage->status == 1 ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="0" {{ $instituteImage->status == 0 ? 'selected' : '' }}>
                                Inactive
                            </option>

                        </select>

                    </div>

                </div>

                <button class="btn btn-primary mt-3">
                    Update
                </button>

            </form>

        </div>
    </div>
@endsection

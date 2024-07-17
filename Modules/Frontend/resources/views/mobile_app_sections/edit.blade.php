@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Mobile App Section') }}
@endsection

@section('create-button')
    <a href="{{ route('mobile-app-sections.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All List') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('mobile-app-sections.update', $id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <input type="text" name="title" value="{{ $mobileAppSection->title }}" class="form-control mb-2">

                <input type="text" name="heading" value="{{ $mobileAppSection->heading }}" class="form-control mb-2">

                <textarea name="description" class="form-control mb-2">{{ $mobileAppSection->description }}</textarea>

                @if ($mobileAppSection->image)
                    <img src="{{ asset('storage/mobile_app_sections/' . $mobileAppSection->image) }}" width="80">
                @endif

                <input type="file" name="image" class="form-control mb-2">

                <input type="text" name="feature_one" value="{{ $mobileAppSection->feature_one }}"
                    class="form-control mb-2">

                <input type="text" name="feature_two" value="{{ $mobileAppSection->feature_two }}"
                    class="form-control mb-2">

                <input type="text" name="feature_three" value="{{ $mobileAppSection->feature_three }}"
                    class="form-control mb-2">

                <input type="text" name="play_store_link" value="{{ $mobileAppSection->play_store_link }}"
                    class="form-control mb-2">

                <input type="text" name="app_store_link" value="{{ $mobileAppSection->app_store_link }}"
                    class="form-control mb-2">

                <button class="btn btn-primary">Update</button>

            </form>

        </div>
    </div>
@endsection

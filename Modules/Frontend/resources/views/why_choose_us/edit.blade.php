@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Why Choose Us') }}
@endsection

@section('create-button')
    <a href="{{ route('why-choose-us.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All List') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('why-choose-us.update', $id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="{{ $whyChoose->title }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control">{{ $whyChoose->description }}</textarea>
                </div>

                <div class="form-group">
                    <label>Icon</label><br>

                    @if ($whyChoose->icon)
                        <img src="{{ asset('storage/why_choose_us/' . $whyChoose->icon) }}" width="50" height="50">
                    @endif

                    <input type="file" name="icon" class="form-control mt-2">
                </div>

                <button class="btn btn-primary mt-3">
                    Update
                </button>

            </form>

        </div>
    </div>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Why Choose Us') }}
@endsection

@section('create-button')
    <a href="{{ route('why-choose-us.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All List') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('why-choose-us.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Icon</label>
                    <input type="file" name="icon" class="form-control">
                </div>

                <button class="btn btn-primary mt-3">
                    Save
                </button>

            </form>

        </div>
    </div>
@endsection

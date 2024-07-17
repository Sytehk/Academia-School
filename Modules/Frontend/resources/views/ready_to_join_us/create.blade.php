@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Ready To Join Us') }}
@endsection

@section('create-button')
    <a href="{{ route('ready-to-join-us.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Items') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('ready-to-join-us.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group mb-3">
                    <label>{{ _lang('Title') }}</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Description') }}</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Icon') }}</label>
                    <input type="file" name="icon" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Button Name') }}</label>
                    <input type="text" name="button_name" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label>{{ _lang('Button Link') }}</label>
                    <input type="text" name="button_link" class="form-control">
                </div>

                <button class="btn btn-primary mt-3">
                    {{ _lang('Save') }}
                </button>

            </form>

        </div>
    </div>
@endsection

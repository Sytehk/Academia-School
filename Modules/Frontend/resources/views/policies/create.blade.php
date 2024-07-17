@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Policy') }}
@endsection

@section('create-button')
    <a href="{{ route('policies.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Policies') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('policies.store') }}">
                @csrf

                <div class="form-group">
                    <label>Type</label>
                    <select name="type" class="form-control" required>
                        <option value="1">{{ _lang('Privacy Policy') }}</option>
                        <option value="2">{{ _lang('Cookie Policy') }}</option>
                        <option value="3">{{ _lang('Terms & Conditions') }}</option>
                        <option value="4">{{ _lang('Refund Policy') }}</option>
                        <option value="5">{{ _lang('Legal') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ _lang('Description') }}</label>
                    <textarea name="description" id="editor" class="form-control" rows="10">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="1">Active</option>
                        <option value="2">Inactive</option>
                    </select>
                </div>

                <button class="btn btn-primary mt-3">
                    Save
                </button>

            </form>

        </div>
    </div>

    <script src="{{ asset('assets/vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
        ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));
    </script>
@endsection

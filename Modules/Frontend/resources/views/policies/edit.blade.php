@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Policy') }}
@endsection

@section('create-button')
    <a href="{{ route('policies.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Policies') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('policies.update', $id) }}">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label>{{ _lang('Type') }}</label>
                    <input type="text" class="form-control" value="{{ \Modules\Frontend\Models\Policy::titleFor((int) $policy->type) }}" disabled>
                    <small class="text-muted">{{ _lang('Default policy pages can be edited only.') }}</small>
                </div>

                <div class="form-group">
                    <label>{{ _lang('Description') }}</label>
                    <textarea name="description" id="editor" class="form-control" rows="10">{{ old('description', $policy->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label>{{ _lang('Status') }}</label>
                    <select name="status" class="form-control">
                        <option value="1" {{ (int) old('status', $policy->status) === 1 ? 'selected' : '' }}>{{ _lang('Active') }}</option>
                        <option value="2" {{ (int) old('status', $policy->status) === 2 ? 'selected' : '' }}>{{ _lang('Inactive') }}</option>
                    </select>
                </div>

                <button class="btn btn-primary mt-3">
                    {{ _lang('Update') }}
                </button>

            </form>

        </div>
    </div>

    <script src="{{ asset('assets/vendor/ckeditor/ckeditor.js') }}"></script>
    <script>
        ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));
    </script>
@endsection

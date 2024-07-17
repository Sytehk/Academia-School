@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create FAQ') }}
@endsection

@section('create-button')
    <a href="{{ route('faqs.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All FAQs') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('faqs.store') }}">
                @csrf

                <div class="form-group">
                    <label class="my-2">Question</label>
                    <input type="text" name="question" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="my-2">Answer</label>
                    <textarea name="answer" class="form-control" rows="5"></textarea>
                </div>

                <div class="form-group">
                    <label class="my-2">Status</label>
                    <select name="status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <button class="btn btn-primary mt-4">
                    Save FAQ
                </button>

            </form>

        </div>
    </div>
@endsection

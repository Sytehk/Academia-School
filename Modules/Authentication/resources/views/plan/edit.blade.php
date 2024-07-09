@extends('layouts.backend')

@section('page_title_breadcrumb')
    Edit Plan
@endsection

@section('create-button')
    <a href="{{ route('plans.index') }}" class="btn btn-sm fw-bold btn-primary">
        Plan List
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">

            <form action="{{ route('plans.update', $plan->id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" class="form-control"
                            value="{{ old('price', $plan->price) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Student Limit</label>
                        <input type="number" name="student_limit" class="form-control"
                            value="{{ old('student_limit', $plan->student_limit) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Branch Limit</label>
                        <input type="number" name="branch_limit" class="form-control"
                            value="{{ old('branch_limit', $plan->branch_limit) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Duration Days</label>
                        <input type="number" name="duration_days" class="form-control"
                            value="{{ old('duration_days', $plan->duration_days) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ $plan->status == 1 ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="2" {{ $plan->status == 2 ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Is Free</label>
                        <select name="is_free" class="form-control">
                            <option value="0" {{ !$plan->is_free ? 'selected' : '' }}>No</option>
                            <option value="1" {{ $plan->is_free ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Custom Plan</label>
                        <select name="is_custom" class="form-control">
                            <option value="0" {{ !$plan->is_custom ? 'selected' : '' }}>No</option>
                            <option value="1" {{ $plan->is_custom ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description" rows="5" class="form-control">{{ old('description', $plan->description) }}</textarea>
                    </div>

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            Update Plan
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
@endsection

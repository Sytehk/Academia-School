@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Hostel Bill') }}
@endsection

@section('content')
    <form action="{{ route('hostel-bills.store') }}" method="POST">
        @csrf

        <div class="row">

            <div class="col-md-6 mb-4">
                <label class="form-label">
                    {{ _lang('Student') }}
                    <span class="text-danger">*</span>
                </label>

                <select name="student_id" class="form-control" required>

                    <option value="">
                        {{ _lang('Select Student') }}
                    </option>

                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">
                            {{ $student->first_name }}
                            {{ $student->last_name }}
                        </option>
                    @endforeach

                </select>

                @error('student_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label">
                    {{ _lang('Hostel Fee') }}
                </label>

                <input type="number" step="0.01" name="hostel_fee" class="form-control" value="{{ old('hostel_fee') }}"
                    required>

                @error('hostel_fee')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label">
                    {{ _lang('Meal Fee') }}
                </label>

                <input type="number" step="0.01" name="meal_fee" class="form-control" value="{{ old('meal_fee') }}"
                    required>

                @error('meal_fee')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label">
                    {{ _lang('Total Amount') }}
                </label>

                <input type="number" step="0.01" name="total_amount" class="form-control"
                    value="{{ old('total_amount') }}" required>

                @error('total_amount')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label">
                    {{ _lang('Due Date') }}
                </label>

                <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}" required>

                @error('due_date')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

        </div>

        <button type="submit" class="btn btn-primary">
            {{ _lang('Create Hostel Bill') }}
        </button>

    </form>
@endsection

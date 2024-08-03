@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Hostel Bill') }}
@endsection

@section('content')
    <form action="{{ route('hostel-bills.update', $bill->id) }}" method="POST">

        @csrf
        @method('PATCH')

        <div class="row">

            <div class="col-md-6 mb-4">
                <label class="form-label">
                    {{ _lang('Student') }}
                </label>

                <select name="student_id" class="form-control" required>

                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" {{ $bill->student_id == $student->id ? 'selected' : '' }}>
                            {{ $student->first_name }}
                            {{ $student->last_name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label">
                    {{ _lang('Hostel Fee') }}
                </label>

                <input type="number" step="0.01" name="hostel_fee" class="form-control"
                    value="{{ old('hostel_fee', $bill->hostel_fee) }}" required>
            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label">
                    {{ _lang('Meal Fee') }}
                </label>

                <input type="number" step="0.01" name="meal_fee" class="form-control"
                    value="{{ old('meal_fee', $bill->meal_fee) }}" required>
            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label">
                    {{ _lang('Total Amount') }}
                </label>

                <input type="number" step="0.01" name="total_amount" class="form-control"
                    value="{{ old('total_amount', $bill->total_amount) }}" required>
            </div>

            <div class="col-md-6 mb-4">
                <label class="form-label">
                    {{ _lang('Due Date') }}
                </label>

                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $bill->due_date) }}"
                    required>
            </div>

        </div>

        <button type="submit" class="btn btn-primary">
            {{ _lang('Update Hostel Bill') }}
        </button>

    </form>
@endsection

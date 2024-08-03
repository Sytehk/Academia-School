@extends('layouts.backend')

@section('content')
    <form method="POST" action="{{ route('meal-entries.store') }}">
        @csrf

        <label>Student</label>
        <select name="student_id" class="form-control">
            <option>Select Student</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}">
                    {{ $student->user->name ?? '' }}
                </option>
            @endforeach
        </select>

        <label class="mt-3">Meal</label>
        <select name="meal_id" class="form-control">
            <option>Select Meal</option>
            @foreach ($meals as $meal)
                <option value="{{ $meal->id }}">
                    {{ $meal->meal_name }}
                </option>
            @endforeach
        </select>

        <label class="mt-3">Date</label>
        <input type="date" name="date" class="form-control">

        <label class="mt-3">Meal Price</label>
        <input type="number" name="meal_price" class="form-control">

        <button class="btn btn-primary mt-4">Save</button>

    </form>
@endsection

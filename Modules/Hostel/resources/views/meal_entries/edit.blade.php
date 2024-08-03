@extends('layouts.backend')

@section('content')
    <form method="POST" action="{{ route('meal-entries.update', $mealEntry->id) }}">
        @csrf
        @method('PUT')

        <label>Student</label>
        <select name="student_id" class="form-control">

            @foreach ($students as $student)
                <option value="{{ $student->id }}" @if ($student->id == $mealEntry->student_id) selected @endif>
                    {{ $student->user->name ?? '' }}
                </option>
            @endforeach

        </select>

        <label class="mt-3">Meal</label>
        <select name="meal_id" class="form-control">

            @foreach ($meals as $meal)
                <option value="{{ $meal->id }}" @if ($meal->id == $mealEntry->meal_id) selected @endif>
                    {{ $meal->meal_name }}
                </option>
            @endforeach

        </select>

        <label class="mt-3">Date</label>
        <input type="date" name="date" class="form-control" value="{{ $mealEntry->date }}">

        <label class="mt-3">Meal Price</label>
        <input type="number" name="meal_price" class="form-control" value="{{ $mealEntry->meal_price }}">

        <button class="btn btn-primary mt-4">Update</button>

    </form>
@endsection

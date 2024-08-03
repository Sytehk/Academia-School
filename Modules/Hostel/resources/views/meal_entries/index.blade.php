@extends('layouts.backend')

@section('create-button')
    <a href="{{ route('meal-entries.create') }}" class="btn btn-primary btn-sm">
        Add Meal Entry
    </a>
@endsection

@section('content')
    <table class="table">

        <thead>
            <tr>
                <th>SL</th>
                <th>Student</th>
                <th>Meal</th>
                <th>Date</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($mealEntries as $key => $entry)
                <tr>

                    <td>{{ $mealEntries->firstItem() + $key }}</td>

                    <td>{{ $entry->student->user->name ?? '' }}</td>

                    <td>{{ $entry->meal->meal_name ?? '' }}</td>

                    <td>{{ $entry->date }}</td>

                    <td>{{ $entry->meal_price }}</td>

                    <td>

                        <a href="{{ route('meal-entries.edit', $entry->id) }}" class="btn btn-warning btn-sm">Edit</a>

                        <form action="{{ route('meal-entries.destroy', $entry->id) }}" method="POST" style="display:inline">

                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">
                                Delete
                            </button>

                        </form>

                    </td>

                </tr>
            @endforeach

        </tbody>

    </table>

    {{ $mealEntries->links() }}
@endsection

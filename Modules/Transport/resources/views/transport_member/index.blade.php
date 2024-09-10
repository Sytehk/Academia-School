@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Transport Members') }}
@endsection

@section('create-button')
    <a href="{{ route('transport-members.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Member') }}
    </a>
@endsection

@section('content')
    <div class="table-responsive">

        <table class="table table-bordered data-table">

            <thead>
                <tr>
                    <th>SL</th>
                    <th>Student</th>
                    <th>Route</th>
                    <th>Stop</th>
                    <th>Fare</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>


            <tbody>

                @foreach ($members as $key => $member)
                    <tr>

                        <td>{{ $key + 1 }}</td>


                        {{-- STUDENT --}}
                        <td>
                            {{ $member->first_name }} {{ $member->last_name }}
                        </td>


                        {{-- ROUTE --}}
                        <td>
                            {{ $member->route_name ?? 'N/A' }}
                        </td>


                        {{-- STOP --}}
                        <td>
                            {{ $member->stop_name ?? 'N/A' }}
                        </td>


                        {{-- FARE --}}
                        <td>
                            {{ $member->fare_amount }}
                        </td>


                        {{-- STATUS --}}
                        <td>
                            @if ($member->status == 1)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>


                        {{-- ACTION --}}
                        <td>

                            <a href="{{ route('transport-members.edit', $member->id) }}" class="btn btn-sm btn-primary">
                                Edit
                            </a>


                            <form action="{{ route('transport-members.destroy', $member->id) }}" method="POST"
                                class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>
                @endforeach

            </tbody>

        </table>

    </div>
@endsection

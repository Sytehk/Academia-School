@extends('layouts.backend')

@section('page_title_breadcrumb')
    Plan List
@endsection

@section('create-button')
    <a href="{{ route('plans.create') }}" class="btn btn-sm fw-bold btn-primary">
        Create Plan
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Students</th>
                        <th>Branch Limit</th>
                        <th>Duration Days</th>
                        <th>Free</th>
                        <th>Status</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($plans as $key => $plan)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $plan->name }}</td>
                            <td>{{ $plan->price }}</td>
                            <td>{{ $plan->student_limit }}</td>
                            <td>{{ $plan->branch_limit }}</td>
                            <td>{{ $plan->duration_days }} Days</td>
                            <td>
                                {!! $plan->is_free ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' !!}
                            </td>
                            <td>
                                {!! $plan->status == 1
                                    ? '<span class="badge bg-success">Active</span>'
                                    : '<span class="badge bg-danger">Inactive</span>' !!}
                            </td>

                            <td>
                                <a href="{{ route('plans.edit', $plan->id) }}" class="btn btn-sm btn-warning">
                                    Edit
                                </a>

                                <form action="{{ route('plans.destroy', $plan->id) }}" method="POST"
                                    style="display:inline-block">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                        class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                No Data Found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
@endsection

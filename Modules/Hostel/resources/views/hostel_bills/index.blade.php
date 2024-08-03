@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Hostel Bills') }}
@endsection

@section('create-button')
    <a href="{{ route('hostel-bills.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add Hostel Bill') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">

                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr>
                            <th>{{ _lang('SL') }}</th>
                            <th>{{ _lang('Student') }}</th>
                            <th>{{ _lang('Hostel Fee') }}</th>
                            <th>{{ _lang('Meal Fee') }}</th>
                            <th>{{ _lang('Total Amount') }}</th>
                            <th>{{ _lang('Due Date') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($bills as $key => $bill)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $bill->student->first_name ?? '' }}
                                    {{ $bill->student->last_name ?? '' }}
                                </td>

                                <td>{{ $bill->hostel_fee }}</td>
                                <td>{{ $bill->meal_fee }}</td>
                                <td>{{ $bill->total_amount }}</td>
                                <td>{{ $bill->due_date }}</td>

                                <td>
                                    <a href="{{ route('hostel-bills.edit', $bill->id) }}" class="btn btn-sm btn-warning">
                                        Edit
                                    </a>

                                    <form action="{{ route('hostel-bills.destroy', $bill->id) }}" method="POST"
                                        style="display:inline-block">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    {{ _lang('No Data Found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

                {{ $bills->links() }}

            </div>
        </div>
    </div>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Room Members') }}
@endsection

@section('create-button')
    <a href="{{ route('room-members.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add Room Member') }}
    </a>
@endsection

@section('content')
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>{{ _lang('SL') }}</th>
                    <th>{{ _lang('Student') }}</th>
                    <th>{{ _lang('Phone') }}</th>
                    <th>{{ _lang('Room') }}</th>
                    <th>{{ _lang('Hostel Category') }}</th>
                    <th>{{ _lang('Action') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse($members as $key => $member)
                    <tr>
                        <td>{{ $key + 1 }}</td>

                        <td>
                            {{ $member->student->first_name ?? '' }}
                        </td>

                        <td>
                            {{ $member->student->phone ?? '' }}
                        </td>

                        <td>
                            {{ $member->room->room_number ?? '' }}
                        </td>

                        <td>
                            {{ $member->room->hostelCategory->standard ?? '' }}
                        </td>

                        <td>
                            <a href="{{ route('room-members.edit', $member->id) }}" class="btn btn-warning btn-sm">
                                Edit
                            </a>

                            <form action="{{ route('room-members.destroy', $member->id) }}" method="POST"
                                style="display:inline">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            No Data Found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $members->links() }}
    </div>
@endsection

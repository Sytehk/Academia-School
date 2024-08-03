@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Rooms') }}
@endsection

@section('create-button')
    <a href="{{ route('rooms.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Room') }}
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
                            <th>{{ _lang('Room Number') }}</th>
                            <th>{{ _lang('Hostel Category') }}</th>
                            <th>{{ _lang('Capacity') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($rooms as $key => $room)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $room->room_number }}</td>
                                <td>{{ $room->hostelCategory?->standard ?? '-' }}</td>
                                <td>{{ $room->capacity }}</td>

                                <td>
                                    <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-sm btn-warning">
                                        Edit
                                    </a>

                                    <form action="{{ route('rooms.destroy', $room->id) }}" method="POST"
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
                                <td colspan="5" class="text-center">
                                    {{ _lang('No Data Found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $rooms->links() }}
            </div>
        </div>
    </div>
@endsection

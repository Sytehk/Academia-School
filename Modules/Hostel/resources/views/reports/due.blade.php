@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Hostel Due Report') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ _lang('Student') }}</th>
                            <th>{{ _lang('Room') }}</th>
                            <th>{{ _lang('Due Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            @php $due = $member->collections->sum('total_due'); @endphp
                            <tr>
                                <td>{{ $member->id }}</td>
                                <td>{{ $member->student?->user?->name ?? '-' }}</td>
                                <td>{{ $member->room?->room_number ?? '-' }}</td>
                                <td><span class="badge bg-warning">{{ $due }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">{{ _lang('No dues found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

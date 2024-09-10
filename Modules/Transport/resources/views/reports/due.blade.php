@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Transport Due Report') }}
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
                            <th>{{ _lang('Route') }}</th>
                            <th>{{ _lang('Stop') }}</th>
                            <th>{{ _lang('Due Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            @php $due = $member->collections->where('total_due', '>', 0)->sum('total_due'); @endphp
                            <tr>
                                <td>{{ $member->id }}</td>
                                <td>{{ $member->student?->user?->name ?? '-' }}</td>
                                <td>{{ $member->route?->route_name ?? '-' }}</td>
                                <td>{{ $member->stop?->stop_name ?? '-' }}</td>
                                <td><span class="badge bg-warning">{{ $due }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">{{ _lang('No dues found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

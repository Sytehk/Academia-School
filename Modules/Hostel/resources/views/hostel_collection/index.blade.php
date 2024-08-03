@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Hostel Fee Collection') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Month') }}</label>
                    <input type="month" name="month" class="form-control" value="{{ $currentMonth }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ _lang('Search (student or room)') }}</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100">{{ _lang('Filter') }}</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ _lang('Student') }}</th>
                            <th>{{ _lang('Room') }}</th>
                            <th>{{ _lang('Bed') }}</th>
                            <th>{{ _lang('This Month') }}</th>
                            <th>{{ _lang('All Outstanding Due') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td>{{ $member->id }}</td>
                                <td>{{ $member->student?->user?->name ?? '-' }}</td>
                                <td>{{ $member->room?->room_number ?? '-' }}</td>
                                <td>{{ $member->bed?->bed_number ?? '-' }}</td>
                                <td>
                                    @php $collection = $member->collections->first(); @endphp
                                    @if ($collection)
                                        <span class="badge bg-{{ $collection->total_due > 0 ? 'warning' : 'success' }}">
                                            {{ _lang('Paid') }}: {{ $collection->total_paid }} / {{ _lang('Due') }}: {{ $collection->total_due }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">{{ _lang('Not Collected') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if (($member->total_outstanding_due ?? 0) > 0)
                                        <span class="badge bg-danger">{{ format_currency($member->total_outstanding_due) }}</span>
                                    @else
                                        <span class="badge bg-success">{{ _lang('Clear') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @can('hostel_collection_create')
                                        <a href="{{ route('hostel-collections.create', $member->id) }}" class="btn btn-sm btn-primary">{{ _lang('Collect') }}</a>
                                    @endcan
                                    @if ($collection)
                                        <a href="{{ route('hostel-collections.invoice', $collection->id) }}" class="btn btn-sm btn-dark" target="_blank">{{ _lang('Receipt') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">{{ _lang('No hostel members found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

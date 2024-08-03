@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Room-wise Collection Report') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @forelse ($collections as $groupName => $group)
                <h5 class="mt-3">{{ $groupName }} — {{ _lang('Total') }}: {{ format_currency($group->sum('total_paid')) }}</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>{{ _lang('Invoice') }}</th>
                                <th>{{ _lang('Student') }}</th>
                                <th>{{ _lang('Paid') }}</th>
                                <th>{{ _lang('Due') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group as $collection)
                                <tr>
                                    <td>{{ $collection->invoice_id }}</td>
                                    <td>{{ $collection->student?->user?->name ?? '-' }}</td>
                                    <td>{{ $collection->total_paid }}</td>
                                    <td>{{ $collection->total_due }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <p class="text-center">{{ _lang('No collections found.') }}</p>
            @endforelse
        </div>
    </div>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Library') }}
@endsection

@section('content')
    @include('parentmodule::partials.child_context')

    @if (empty($library['member']))
        <div class="card">
            <div class="card-body text-center py-10">
                <i class="fa-solid fa-book-open-reader fs-3x text-muted mb-3 d-block"></i>
                <div class="text-muted">{{ _lang('This child is not a library member.') }}</div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-header align-items-center">
                <h3 class="card-title">{{ _lang('Library History') }}</h3>
                <div class="card-toolbar text-muted fs-7">
                    {{ _lang('Member') }}: <b>{{ $library['member']->name ?? '—' }}</b>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-dashed gy-2 align-middle">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4">{{ _lang('Book') }}</th>
                                <th>{{ _lang('Code') }}</th>
                                <th>{{ _lang('Category') }}</th>
                                <th>{{ _lang('Issue Date') }}</th>
                                <th>{{ _lang('Due Date') }}</th>
                                <th class="pe-4">{{ _lang('Return Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($library['issues'] as $issue)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $issue->book_name }}</td>
                                    <td>{{ $issue->code ?: '—' }}</td>
                                    <td>{{ $issue->category ?: '—' }}</td>
                                    <td>{{ $issue->issue_date ? \Carbon\Carbon::parse($issue->issue_date)->format('d M, Y') : '—' }}</td>
                                    <td>{{ $issue->due_date ? \Carbon\Carbon::parse($issue->due_date)->format('d M, Y') : '—' }}</td>
                                    <td class="pe-4">
                                        @if ($issue->return_date)
                                            <span class="badge badge-light-success">{{ \Carbon\Carbon::parse($issue->return_date)->format('d M, Y') }}</span>
                                        @else
                                            <span class="badge badge-light-warning">{{ _lang('Not Returned') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-8">{{ _lang('No book issue history.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection

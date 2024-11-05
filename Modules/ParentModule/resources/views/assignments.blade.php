@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Assignments') }}
@endsection

@section('content')
    @include('parentmodule::partials.child_context')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ _lang('Assignments') }}</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-dashed gy-2 align-middle">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4">#</th>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Subject') }}</th>
                            <th>{{ _lang('Deadline') }}</th>
                            <th class="pe-4">{{ _lang('Files') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignments as $index => $assignment)
                            <tr>
                                <td class="ps-4">{{ $assignments->firstItem() + $index }}</td>
                                <td>
                                    <span class="fw-semibold d-block">{{ $assignment->title }}</span>
                                    @if ($assignment->description)
                                        <span class="text-muted fs-8">{{ \Illuminate\Support\Str::limit(strip_tags($assignment->description), 80) }}</span>
                                    @endif
                                </td>
                                <td>{{ optional($assignment->subject)->subject_name ?? '—' }}</td>
                                <td>
                                    @if ($assignment->deadline)
                                        <span class="badge badge-light-{{ \Carbon\Carbon::parse($assignment->deadline)->isPast() ? 'danger' : 'success' }}">
                                            {{ \Carbon\Carbon::parse($assignment->deadline)->format('d M, Y') }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="pe-4">
                                    @foreach (['file', 'file_2', 'file_3', 'file_4'] as $i => $col)
                                        @if ($assignment->{$col})
                                            <a class="btn btn-sm btn-icon btn-light-primary me-1"
                                                target="_blank"
                                                href="{{ asset('uploads/files/assignments/' . $assignment->{$col}) }}"
                                                title="{{ _lang('Download') }} {{ $i + 1 }}">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-8">{{ _lang('No assignments found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($assignments->hasPages())
                <div class="mt-4">{{ $assignments->links() }}</div>
            @endif
        </div>
    </div>
@endsection

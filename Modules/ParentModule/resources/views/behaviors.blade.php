@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Behaviour') }}
@endsection

@section('content')
    @include('parentmodule::partials.child_context')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ _lang('Behaviour Records') }}</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-dashed gy-2 align-middle">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4">{{ _lang('Date') }}</th>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Note') }}</th>
                            <th class="pe-4">{{ _lang('File') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($behaviors as $behavior)
                            <tr>
                                <td class="ps-4">{{ $behavior->date ? \Carbon\Carbon::parse($behavior->date)->format('d M, Y') : '—' }}</td>
                                <td class="fw-semibold">{{ $behavior->title }}</td>
                                <td>{{ $behavior->note ?: '—' }}</td>
                                <td class="pe-4">
                                    @if ($behavior->file)
                                        <a class="btn btn-sm btn-icon btn-light-primary" target="_blank"
                                            href="{{ asset('storage/behaviors/' . $behavior->file) }}">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-8">{{ _lang('No behaviour records found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($behaviors->hasPages())
                <div class="mt-4">{{ $behaviors->links() }}</div>
            @endif
        </div>
    </div>
@endsection

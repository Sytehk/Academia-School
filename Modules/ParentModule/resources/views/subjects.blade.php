@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Subjects') }}
@endsection

@section('content')
    @include('parentmodule::partials.child_context')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ _lang('Subjects') }}</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-dashed gy-2 align-middle">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4">#</th>
                            <th>{{ _lang('Subject Name') }}</th>
                            <th>{{ _lang('Subject Code') }}</th>
                            <th class="pe-4">{{ _lang('Type') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subjects as $index => $subject)
                            <tr>
                                <td class="ps-4">{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $subject->subject_name }}</td>
                                <td>{{ $subject->subject_code ?: '—' }}</td>
                                <td class="pe-4">
                                    <span class="badge badge-light-primary">{{ $subject->subject_type ?: '—' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-8">{{ _lang('No subjects found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

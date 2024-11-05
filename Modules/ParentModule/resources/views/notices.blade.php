@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Notice Board') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ _lang('Notice Board') }}</h3>
        </div>
        <div class="card-body">
            @forelse ($notices as $notice)
                <div class="d-flex align-items-start border border-dashed rounded p-4 mb-4">
                    <span class="bullet bullet-vertical h-auto align-self-stretch bg-primary me-4"></span>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between flex-wrap">
                            <span class="fw-bold fs-5 text-gray-900">{{ $notice->title }}</span>
                            <span class="text-muted fs-8">{{ \Carbon\Carbon::parse($notice->date)->format('d M, Y') }}</span>
                        </div>
                        <div class="text-muted mt-2">{!! nl2br(e(strip_tags($notice->notice))) !!}</div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-10">{{ _lang('No notices found.') }}</div>
            @endforelse

            @if ($notices->hasPages())
                <div class="mt-4">{{ $notices->links() }}</div>
            @endif
        </div>
    </div>
@endsection

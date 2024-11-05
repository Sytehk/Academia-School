@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Unpaid Fees') }}
@endsection

@section('content')
    @include('parentmodule::partials.child_context')

    <div class="card">
        <div class="card-header align-items-center">
            <h3 class="card-title">{{ _lang('Unpaid Invoice') }}</h3>
            <div class="card-toolbar">
                <span class="fs-5 fw-bold text-danger">
                    {{ _lang('Total Due') }}: {{ format_currency($fees['total_due_raw'] ?? 0) }}
                </span>
            </div>
        </div>
        <div class="card-body">
            @forelse ($fees['fee_heads'] as $head)
                <div class="mb-6">
                    <h4 class="fw-bold text-gray-800 mb-3">{{ $head['fee_head_name'] }}</h4>
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-dashed gy-2 align-middle">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4">{{ _lang('Sub Head') }}</th>
                                    <th class="text-end pe-4">{{ _lang('Payable') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($head['sub_heads'] as $sub)
                                    <tr>
                                        <td class="ps-4">{{ $sub['sub_head_name'] }}</td>
                                        <td class="text-end pe-4 fw-semibold">{{ format_currency($sub['payable']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <i class="fa-solid fa-circle-check text-success fs-3x mb-3 d-block"></i>
                    <div class="text-muted">{{ _lang('No unpaid fees. All dues are cleared.') }}</div>
                </div>
            @endforelse

            {{-- Online payment entry point — wired in the Online Fee Payment module (section 4). --}}
            @if (!empty($fees['fee_heads']))
                <div class="alert alert-primary d-flex align-items-center mt-3">
                    <i class="fa-solid fa-circle-info fs-2 text-primary me-3"></i>
                    <div>{{ _lang('Online payment for these dues will be available here.') }}</div>
                </div>
            @endif
        </div>
    </div>
@endsection

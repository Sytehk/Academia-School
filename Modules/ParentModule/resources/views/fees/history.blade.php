@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Payment History') }}
@endsection

@section('content')
    @include('parentmodule::partials.child_context')

    <div class="card">
        <div class="card-header align-items-center">
            <h3 class="card-title">{{ _lang('Payment History') }}</h3>
            <div class="card-toolbar">
                <span class="fs-5 fw-bold text-success">
                    {{ _lang('Total Paid') }}: {{ $payment['total_paid'] }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-dashed gy-2 align-middle">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4">{{ _lang('Invoice') }}</th>
                            <th>{{ _lang('Date') }}</th>
                            <th>{{ _lang('Fee Head') }}</th>
                            <th class="text-end">{{ _lang('Paid') }}</th>
                            <th class="text-end">{{ _lang('Waiver') }}</th>
                            <th class="text-end">{{ _lang('Fine') }}</th>
                            <th class="text-end pe-4">{{ _lang('Prev. Due Paid') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payment['history'] as $row)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $row->invoice_id }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->invoice_date)->format('d M, Y') }}</td>
                                <td>{{ $row->fee_head_name ?? '—' }}</td>
                                <td class="text-end">{{ format_currency($row->fee_and_fine_paid) }}</td>
                                <td class="text-end">{{ format_currency($row->waiver) }}</td>
                                <td class="text-end">{{ format_currency($row->fine_payable) }}</td>
                                <td class="text-end pe-4">{{ format_currency($row->previous_due_paid) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-8">{{ _lang('No payment history found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

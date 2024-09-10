@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Collect Transport Fee') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <p>
                <strong>{{ _lang('Student') }}:</strong> {{ $transportMember->student?->user?->name ?? '-' }}
                &nbsp; <strong>{{ _lang('Route') }}:</strong> {{ $transportMember->route?->route_name ?? '-' }}
                &nbsp; <strong>{{ _lang('Stop') }}:</strong> {{ $transportMember->stop?->stop_name ?? '-' }}
            </p>

            <form method="POST" action="{{ route('transport-collections.store') }}">
                @csrf
                <input type="hidden" name="transport_member_id" value="{{ $transportMember->id }}">
                <input type="hidden" name="session_id" value="{{ get_option('academic_year') }}">

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Month') }}</label>
                        <input type="month" name="month" class="form-control" value="{{ now()->format('Y-m') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Fee Amount') }}</label>
                        <input type="number" step="0.01" name="fee_amount" class="form-control" value="{{ $defaultFee }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Discount') }}</label>
                        <input type="number" step="0.01" name="discount" class="form-control" value="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Fine') }}</label>
                        <input type="number" step="0.01" name="fine" class="form-control" value="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Amount Paid') }}</label>
                        <input type="number" step="0.01" name="total_paid" class="form-control" value="{{ $defaultFee }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ _lang('Income Ledger') }}</label>
                        <select name="ledger_id" class="form-control">
                            <option value="">{{ _lang('Select Ledger') }}</option>
                            @foreach ($ledgers as $ledger)
                                <option value="{{ $ledger->id }}" @selected($feeConfig && $feeConfig->ledger_id == $ledger->id)>{{ $ledger->ledger_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ _lang('Payment Method (Receive Ledger)') }}</label>
                        <select name="receive_ledger_id" class="form-control">
                            <option value="">{{ _lang('Select Ledger') }}</option>
                            @foreach ($ledgers as $ledger)
                                <option value="{{ $ledger->id }}">{{ $ledger->ledger_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ _lang('Fund') }}</label>
                        <select name="fund_id" class="form-control">
                            <option value="">{{ _lang('Select Fund') }}</option>
                            @foreach ($funds as $fund)
                                <option value="{{ $fund->id }}" @selected($feeConfig && $feeConfig->fund_id == $fund->id)>{{ $fund->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">{{ _lang('Note') }}</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <button class="btn btn-primary mt-4">{{ _lang('Collect Fee') }}</button>
            </form>
        </div>
    </div>
@endsection

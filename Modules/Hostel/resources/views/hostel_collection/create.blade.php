@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Collect Hostel Fee') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <p>
                <strong>{{ _lang('Student') }}:</strong> {{ $roomMember->student?->user?->name ?? '-' }}
                &nbsp; <strong>{{ _lang('Room') }}:</strong> {{ $roomMember->room?->room_number ?? '-' }}
                &nbsp; <strong>{{ _lang('Bed') }}:</strong> {{ $roomMember->bed?->bed_number ?? '-' }}
            </p>

            <form method="POST" action="{{ route('hostel-collections.store') }}">
                @csrf
                <input type="hidden" name="room_member_id" value="{{ $roomMember->id }}">
                <input type="hidden" name="session_id" value="{{ get_option('academic_year') }}">

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Month (for monthly fee)') }}</label>
                        <input type="month" name="month" class="form-control" value="{{ now()->format('Y-m') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Admission Fee') }}</label>
                        <input type="number" step="0.01" name="fee_heads[admission]" class="form-control" value="{{ $feeConfig->admission_fee ?? 0 }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Monthly Fee') }}</label>
                        <input type="number" step="0.01" name="fee_heads[monthly]" class="form-control" value="{{ $feeConfig->monthly_fee ?? 0 }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Security Deposit') }}</label>
                        <input type="number" step="0.01" name="fee_heads[security_deposit]" class="form-control" value="{{ $feeConfig->security_deposit ?? 0 }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Utility Charge') }}</label>
                        <input type="number" step="0.01" name="fee_heads[utility]" class="form-control" value="{{ $feeConfig->utility_charge ?? 0 }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Maintenance Charge') }}</label>
                        <input type="number" step="0.01" name="fee_heads[maintenance]" class="form-control" value="{{ $feeConfig->maintenance_charge ?? 0 }}">
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
                        <input type="number" step="0.01" name="total_paid" class="form-control" required>
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

@extends('layouts.backend')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('ledger-book-account') }}" method="get">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <div class="form-group">
                                <div class="col-sm-6">
                                    <label class="control-label">{{ _lang('From Date') }}</label>
                                    <input type="date" class="form-control" name="from_date"
                                        value="{{ $startDate ?? Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-6">
                                    <label class="control-label">{{ _lang('To Date') }}</label>
                                    <input type="date" class="form-control" name="to_date"
                                        value="{{ $endDate ?? date('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="control-label">{{ _lang('Payment Method') }}</label>

                                    <select class="form-control select2" name="ledger_id">
                                        {!! create_option('accounting_ledgers', 'id', 'ledger_name', old('ledger_id')) !!}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group ">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-info mt-4">{{ _lang('Search') }}</button>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

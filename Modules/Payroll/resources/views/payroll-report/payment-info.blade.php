@extends('layouts.backend')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default ">
                <div class="panel-heading">
                    <span class="panel-title">{{ _lang('Cash Book Account') }}</s>
                </div>
                <div class="card-body">
                    <form action="{{ route('payment-info-report') }}" method="get">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="form-group">
                                        <div class="col-sm-6">
                                            <label class="control-label">{{ _lang('From Date') }}</label>
                                            <input type="text" class="form-control datepicker" name="from_date"
                                                value="{{ old('from_date') }}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-sm-6">
                                            <label class="control-label">{{ _lang('To Date') }}</label>
                                            <input type="text" class="form-control datepicker" name="to_date"
                                                value="{{ old('to_date') }}">
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


        </div>
    </div>
@endsection

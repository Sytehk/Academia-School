@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Journal Transaction') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">
                    <span class="text-center card-title">{{ _lang('Account Journal') }}</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('account-journal-report') }}" method="get">

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('From') }}</label>
                                        <input type="date" class="form-control" name="from_date"
                                            value="{{ $fromDate ?? old('from_date') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('To') }}</label>
                                        <input type="date" class="form-control" name="to_date"
                                            value="{{ $toDate ?? date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group mt-2">
                                <div class="col-sm-5">
                                    <button type="submit" class="btn btn-primary mt-3 ml-5">{{ _lang('Search') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

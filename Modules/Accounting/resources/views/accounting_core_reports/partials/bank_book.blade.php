<div class="panel panel-default ">
    <div class="panel-heading">
        <span class="panel-title">{{ _lang('Bank Book Account') }}</s>
    </div>
    <div class="card-body">
        <form action="{{ route('bank-book-account') }}" method="get">
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
                                <input type="date" class="form-control" name="to_date" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label">{{ _lang('Payment Method') }}</label>

                                <select class="form-control select2" name="method_id[]" multiple>
                                    {!! create_option('payment_methods', 'id', 'name', old('method_id')) !!}
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

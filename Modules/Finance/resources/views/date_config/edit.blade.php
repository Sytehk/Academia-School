@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Fee Date Config') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Update Date Config') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('date-config.update', $feeDateConfig->id) }}" autocomplete="off"
                        class="form-horizontal form-groups-bordered" enctype="multipart/form-data" method="post">
                        @csrf
                        @method('PATCH')

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Payable Date') }}</label>
                                <input type="date" class="form-control" name="payable_date"
                                    value="{{ $feeDateConfig->payable_date_start }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Fine Active Date') }}</label>
                                <input type="date" class="form-control" min="1" name="fine_active_date"
                                    value="{{ $feeDateConfig->payable_date_end }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit"
                                    class="btn btn-primary mt-5">{{ _lang('Update Date Config') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

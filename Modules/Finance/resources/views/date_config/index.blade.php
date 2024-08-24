@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Fee Date Config') }}
@endsection
@section('content')
    <div class="row my-2">
        <div class="col-lg-4">
            <form method="post" action="{{ route('date-config.store') }}">
                @csrf

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Academic Year') }}</label>
                            <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                name="academic_year" required>
                                {!! create_option('academic_years', 'id', 'session', get_option('academic_year')) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Fee Head') }}</label>

                            <select name="fee_head_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2">
                                <option value="">Select Head</option>
                                {!! create_option('fee_heads', 'id', 'name', isset($feeMap) ? $feeMap->fee_head_id : old('fee_head_id')) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-12 my-5">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary mt-4">{{ _lang('Search') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        @if (isset($feeMapFeeSubHeads) && count($feeMapFeeSubHeads) > 0)
            <div class="col-lg-8">
                <div class="card-body table-responsive">
                    <form method="post" action="{{ route('fee-date-config.store') }}">
                        @csrf

                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <!--begin::Table head-->
                            <thead>
                                <!--begin::Table row-->
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th width="20">{{ _lang('Fee Sub Head') }}</th>
                                    <th width="60">{{ _lang('Fee Payable Date') }}</th>
                                    <th width="20">{{ _lang('Fine Active Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($feeMapFeeSubHeads))
                                    @foreach ($feeMapFeeSubHeads as $feeMapFeeSubHead)
                                        <tr>
                                            <td>{{ $feeMapFeeSubHead->name }}</td>
                                            <td>
                                                <input type="hidden" name="fee_sub_head_id[]"
                                                    value="{{ $feeMapFeeSubHead->fee_sub_head_id }}">

                                                <input type="date" class="form-control" name="payable_date[]"
                                                    value="{{ old('payable_date') }}">
                                            </td>
                                            <td>
                                                <input type="date" class="form-control" name="fine_active_date[]"
                                                    value="{{ old('fine_active_date') }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>

                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                            Save
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    @if (isset($fee_date_configs) && count($fee_date_configs) > 0)
        <div class="row my-2">
            <div class="col-lg-12">
                <div class="card-body table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <!--begin::Table head-->
                        <thead>
                            <!--begin::Table row-->
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th>{{ _lang('Fee Sub Head') }}</th>
                                <th>{{ _lang('Fee Payable Date') }}</th>
                                <th>{{ _lang('Fine Active Date') }}</th>
                                <th>{{ _lang('Action') }}</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fee_date_configs as $fee_date_config)
                                <tr>
                                    <td>{{ $fee_date_config->fee_sub_head_name }}</td>
                                    <td>
                                        {{ $fee_date_config->payable_date_start }}
                                    </td>
                                    <td>
                                        {{ $fee_date_config->payable_date_end }}
                                    </td>
                                    <td>
                                        <form action="{{ route('date-config.destroy', $fee_date_config->id) }}"
                                            method="post">
                                            <a href="{{ route('date-config.edit', $fee_date_config->id) }}"
                                                class="btn btn-warning btn-sm"><i class="fa fa-pencil"
                                                    aria-hidden="true"></i></a>
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm btn-remove"><i
                                                    class="fa fa-trash" aria-hidden="true"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection

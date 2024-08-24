<div class="row my-2">
    <div class="col-lg-4">
        <div class="table-responsive border p-5">
            <form method="post" action="{{ route('fees-mapping.store') }}">
                @csrf
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            {!! generate_select(
                                'fee_head_id',
                                Modules\Finance\Models\FeeHead::orderBy('serial', 'asc')->pluck('name', 'id')->toArray(),
                                _lang('Fee Head'),
                                old('fee_head_id'),
                                true,
                                _lang('--Select Head--'),
                                'form-select form-select-sm form-select-solid',
                            ) !!}
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            {!! generate_select(
                                'fee_sub_head_ids',
                                Modules\Finance\Models\FeeSubHead::orderBy('serial', 'asc')->pluck('name', 'id')->toArray(),
                                _lang('Fee Sub Heads'),
                                old('fee_sub_head_ids'),
                                true,
                                _lang('--Select Fee Subhead--'),
                                'form-select form-select-sm form-select-solid',
                                null,
                                null,
                                true,
                                true, // multiple
                            ) !!}
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            {!! generate_select(
                                'ledger_id',
                                Modules\Accounting\Models\AccountingLedger::pluck('ledger_name', 'id')->toArray(),
                                _lang('Ledger'),
                                old('ledger_id'),
                                true,
                                _lang('--Select Ledger--'),
                                'form-select form-select-sm form-select-solid',
                            ) !!}
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            {!! generate_select(
                                'fund_ids',
                                Modules\Accounting\Models\AccountingFund::pluck('name', 'id')->toArray(),
                                _lang('Fund'),
                                old('fund_ids'),
                                true,
                                _lang('--Select Fund--'),
                                'form-select form-select-sm form-select-solid',
                                null,
                                null,
                                true,
                                true, // multiple
                            ) !!}
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group mt-5">
                            <input type="hidden" name="type" value="fee">
                            <button type="submit" class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card-body table-responsive border p-5">
            <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                <!--begin::Table head-->
                <thead>
                    <!--begin::Table row-->
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th width="20">{{ _lang('Head') }}</th>
                        <th width="60">{{ _lang('Ledger') }}</th>
                        <th width="20">{{ _lang('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fee_mapping as $fee_map)
                        <tr>
                            <td width="20">{{ $fee_map->feeHead?->name }}</td>
                            <td width="60">{{ $fee_map->feeLedger?->ledger_name }}</td>
                            <td width="20">
                                <a href="{{ route('fees-mapping.edit', $fee_map->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                </a>
                                <form action="{{ route('fees-mapping.destroy', $fee_map->id) }}" method="post"
                                    class="d-inline">
                                    @method('DELETE')
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm show_confirm"><i
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

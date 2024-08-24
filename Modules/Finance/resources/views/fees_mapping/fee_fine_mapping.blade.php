<div class="row my-2">
    <div class="col-lg-4">
        <div class="table-responsive border p-5">
            <form method="post" action="{{ route('fees-mapping.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Fee Head') }}</label>

                            <select name="fee_head_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" required>
                                <option value="">Select Head</option>
                                {!! create_option('fee_heads', 'id', 'name', old('fee_head_id')) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Ledger') }}</label>

                            <select name="ledger_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" required>
                                <option value="">Select Ledger</option>
                                {!! create_option('accounting_ledgers', 'id', 'ledger_name', old('ledger_id')) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Fund') }}</label>

                            <select name="fund_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" required>
                                <option value="">Select Fund</option>
                                {!! create_option('accounting_funds', 'id', 'name', old('fund_id')) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group mt-5">
                            <input type="hidden" name="type" value="fee_fine">
                            <button type="submit" class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <div class="col-lg-8">
        <div class="table-responsive border p-5">
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
                    @foreach ($fee_fine_mapping as $fee_map)
                        <tr>
                            <td width="20">{{ $fee_map->feeHead->name }}</td>
                            <td width="60">{{ $fee_map->feeLedger->ledger_name }}</td>
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

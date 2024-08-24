<div class="row my-2">
    <div class="col-lg-4">
        <div class="table-responsive border p-5">
            <form method="post" action="{{ route('fee-sub-head.store') }}">
                @csrf
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Name') }}</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                placeholder="Fee Head" required>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Serial') }}</label>
                            <input type="number" class="form-control" name="serial" value="{{ old('serial') }}"
                                placeholder="Serial No">
                        </div>
                    </div>

                    <div class="col-lg-12 my-5">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <div class="col-lg-8">
        <div class="table-responsive border p-5">
            <form action="{{ route('fee-sub-head-serial.update') }}" method="POST">
                @csrf

                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <!--begin::Table head-->
                    <thead>
                        <!--begin::Table row-->
                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                            <th width="20">{{ _lang('Serial') }}</th>
                            <th width="60">{{ _lang('Name') }}</th>
                            <th width="20">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fee_sub_heads as $fee_sub_head)
                            <tr id="row_{{ $fee_sub_head->id }}">
                                <td width="20">
                                    <input type="hidden" name="fee_sub_head_ids[]" value="{{ $fee_sub_head->id }}">

                                    <input type="number" name="serials[]" value="{{ $fee_sub_head->serial }}"
                                        class="form-control">
                                </td>

                                <td width="60">{{ $fee_sub_head->name }}</td>
                                <td width="20">
                                    <a href="{{ route('fee-sub-head.delete', $fee_sub_head->id) }}"
                                        class="btn btn-danger btn-sm" type="submit">{{ _lang('Delete') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fa fa-pencil" aria-hidden="true"></i>
                    Serial Update
                </button>
            </form>
        </div>
    </div>
</div>

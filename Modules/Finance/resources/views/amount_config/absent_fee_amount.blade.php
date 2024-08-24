<div class="row my-2">
    <div class="col-lg-4">
        <div class="table-responsive border p-5">
            <form method="post" class="" action="{{ route('absent-fines.store') }}">
                @csrf

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Class') }}</label>
                            <select name="class_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2">
                                {!! create_option('classes', 'id', 'class_name', old('class_id')) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Period') }}</label>
                            <select name="period_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2">
                                {!! create_option('periods', 'id', 'period', old('period_id')) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Amount') }}</label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00"
                                value="{{ old('amount') }}" />
                        </div>
                    </div>

                    <div class="col-lg-12 my-5">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary mt-5">{{ _lang('Save') }}</button>
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
                        <th>{{ _lang('Class') }}</th>
                        <th>{{ _lang('Period') }}</th>
                        <th>{{ _lang('Amount') }}</th>
                        <th>{{ _lang('Action') }}</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($absentFines as $absentFine)
                        <tr>
                            <td>{{ $absentFine->class->class_name }}</td>
                            <td>{{ $absentFine->period->period }}</td>
                            <td>{{ $absentFine->fee_amount }}</td>
                            <td>
                                <a href="{{ route('absent-fines.edit', $absentFine->id) }}"
                                    data-title="{{ _lang('Update Fee Type') }}"
                                    class="btn btn-warning btn-sm ajax-modal">{{ _lang('Edit') }}</a>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>

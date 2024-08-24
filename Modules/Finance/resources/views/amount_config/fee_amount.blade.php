<div class="row my-2">
    <div class="col-lg-12">

        <div class="table-responsive border p-5">
            <form method="post" action="{{ route('fees.store') }}">
                @csrf

                <div class="row">
                    <div class="col-lg-6">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Class') }}</label>
                                    <select name="class_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2" required>

                                        {!! create_option('classes', 'id', 'class_name', old('class')) !!}
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Group') }}</label>
                                    <select name="group_id" id="group"
                                        class="form-select form-select-sm form-select-solid" data-control="select2"
                                        required>

                                        {!! create_option('student_groups', 'id', 'group_name', old('group')) !!}
                                    </select>
                                </div>

                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Student Category') }}</label>
                                    <select name="student_category_id" id="student_category"
                                        class="form-select form-select-sm form-select-solid" data-control="select2"
                                        required>

                                        {!! create_option('student_categories', 'id', 'name', old('name')) !!}
                                    </select>
                                </div>

                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Fee Head') }}</label>

                                    <select name="fee_head_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2" required>
                                        <option value="">Select Head</option>
                                        {!! create_option('fee_heads', 'id', 'name', old('fee_head_id')) !!}
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Fee Amount') }}</label>
                                    <input type="number" step="0.01" name="fee_amount" class="form-control"
                                        placeholder="0.00" value="{{ old('fee_amount') }}" />
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Fine Amount') }}</label>
                                    <input type="number" step="0.01" name="fine_amount" class="form-control"
                                        placeholder="0.00" value="{{ old('fine_amount') }}" />
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <label class="control-label my-2">{{ _lang('Fund') }}</label>
                                <select name="fund_id" id="fund_id"
                                    class="form-select form-select-sm form-select-solid" data-control="select2"
                                    required>
                                    <option value="">{{ _lang('--Select Fund--') }}</option>
                                    {!! create_option('accounting_funds', 'id', 'name', old('fund_id')) !!}
                                </select>
                            </div>

                            <div class="col-lg-12 mt-5">
                                <button type="submit" class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <div class="col-lg-12">
        <div class="card-header">
            <h4 class="card-title">{{ _lang('Assigned Fee List') }}</h4>
        </div>

        <div class="table-responsive border p-5">
            <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                <!--begin::Table head-->
                <thead>
                    <!--begin::Table row-->
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th>{{ _lang('Class') }}</th>
                        <th>{{ _lang('Group') }}</th>
                        <th>{{ _lang('Section') }}</th>
                        <th>{{ _lang('Category') }}</th>
                        <th>{{ _lang('Fee Head') }}</th>
                        <th>{{ _lang('Fee Amount') }}</th>
                        <th>{{ _lang('Fine Amount') }}</th>
                        <th>{{ _lang('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fees as $fee)
                        <tr>
                            <td>{{ $fee->class->class_name }}</td>
                            <td>{{ $fee->group->group_name }}</td>
                            <td>{{ $fee->section?->section_name }}</td>
                            <td>{{ $fee->studentCategory->name }}</td>
                            <td>{{ $fee->feeHead->name ?? null }}</td>
                            <td>{{ $fee->fee_amount }}</td>
                            <td>{{ $fee->fine_amount }}</td>
                            <td>
                                <form action="{{ route('fees.destroy', $fee->id) }}" method="post">
                                    @method('DELETE')
                                    @csrf
                                    <a href="{{ route('fees.edit', $fee->id) }}"
                                        data-title="{{ _lang('Update Fee Type') }}"
                                        class="btn btn-warning btn-sm ajax-modal">{{ _lang('Edit') }}</a>
                                    <input name="_method" type="hidden" value="DELETE">
                                    <button class="btn btn-danger btn-sm btn-remove"
                                        type="submit">{{ _lang('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('js-script')
    <script>
        $(window).on('load', function() {
            $("#section").next().find("ul li").css("display", "none");
            $(document).on("change", "#class", function() {
                $("#section").next().find("ul li").css("display", "none");
                var class_id = $(this).val();
                $('#section option[data-class="' + class_id + '"]').each(function() {
                    var section_id = $(this).val();
                    $("#section").next().find("ul li[data-value='" + section_id + "']").css(
                        "display", "block");
                });
            });
        });
    </script>
@stop

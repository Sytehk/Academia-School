<div class="row my-2">
    <div class="col-lg-12">
        <div class="card-body border">
            <form method="get">
                @csrf

                <input type="hidden" name="type" value="mark_config_view">
                <div class="row justify-content-center">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="control-label mb-2" for="class_id_select">{{ _lang('Class') }}</label>
                            <select name="class_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" required>
                                {!! create_option('classes', 'id', 'class_name', request()->class_id) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="control-label mb-2">{{ _lang('Group') }}</label>
                            <select name="group_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" id="group_id_select">
                                {!! create_option('student_groups', 'id', 'group_name', request()->group_id) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="control-label mb-2">{{ _lang('Exam') }}</label>
                            <select name="exam_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" id="exam_id_select">
                                {!! create_option('exams', 'id', 'name', request()->exam_id) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="form-group">
                            <button type="submit"
                                class="btn btn-primary btn-block margin_top_24">{{ _lang('Search') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($markConfig->isNotEmpty())
    <div class="row my-2">
        <div class="col-lg-12">
            <div class="card-body border">
                <h5 class="card-header">Exam Mark Config Report</h5>

                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <!--begin::Table head-->
                    <thead>
                        <!--begin::Table row-->
                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                            <th style="padding-left: 15px;">Short Code</th>
                            <th>Total</th>
                            <th>Pass</th>
                            <th>Accept</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach ($markConfig->groupBy('subject_id') as $subjectId => $configs)
                            @php
                                $subject = $configs->first()->subject;
                            @endphp
                            <tr>
                                <td colspan="4" class="table-active">
                                    <b style="padding-left: 15px;">Subject Name: {{ $subject->subject_name ?? _lang('Unknown Subject') }}
                                    </b>
                                </td>
                            </tr>
                            @foreach ($configs as $config)
                                @if ($config->mark_config_exam_code)
                                    <tr>
                                        <td style="padding-left: 15px;">{{ $config->mark_config_exam_code->title }}</td>
                                        <td>{{ $config->mark_config_exam_code->total_marks }}</td>
                                        <td>{{ $config->mark_config_exam_code->pass_mark }}</td>
                                        <td>{{ $config->mark_config_exam_code->acceptance }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <p>No mark configuration found for the selected class and group.</p>
@endif

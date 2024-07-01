@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Admit Card And Seat Plan') }}
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ _lang('Generate Seat Plan / Admit Card') }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('exam-essentials.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="my-2 control-label" for="class_id_select">{{ _lang('Class') }}</label>
                                    <select name="class_id" id="class_id_select" class="form-control select2" required>
                                        {!! create_option('classes', 'id', 'class_name', request()->class_id) !!}
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="my-2 control-label">{{ _lang('Section') }}</label>
                                    <select name="section_id" class="form-control select2"
                                        data-selected="{{ request()->section_id }}">
                                        <option value="">{{ _lang('Select Class First') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="my-2 control-label"
                                        for="class_id_select">{{ _lang('From Roll No. (Optional)') }}</label>
                                    <input name="from_roll" type="number" class="form-control"
                                        value="{{ old('from_roll') }}">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="my-2 control-label">{{ _lang('To') }}</label>
                                    <input name="to_roll" type="number" class="form-control"
                                        value="{{ old('to_roll') }}">
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <button type="submit" name="seat_plan" value="1"
                                        class="btn btn-primary btn-block rect-btn"> <i class="fa fa-th-large"
                                            aria-hidden="true"></i> {{ _lang('Seat Plan') }}</button>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <button type="submit" name="admit_card" value="1"
                                        class="btn btn-success btn-block rect-btn"> <i class="fa fa-id-card"
                                            aria-hidden="true"></i> {{ _lang('Admit Card') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        // Section options for this page's class_id select are populated by the
        // global cascade script (resources/views/partials/class_section_group_cascade.blade.php,
        // loaded via layouts.backend) — a second handler here duplicated that
        // same AJAX call on every class change, firing two requests and racing
        // to overwrite the same <select>.
    </script>
@stop

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Grand Final Result') }}
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ _lang('Filter Students') }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('grand-final-result.search') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="my-2 control-label" for="class_id_select">{{ _lang('Class') }}</label>
                                    <select name="class_id" id="class_id_select" class="form-control select2" required>
                                        <option value="">{{ _lang('Select Class') }}</option>
                                        {!! create_option('classes', 'id', 'class_name', request()->class_id) !!}
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="my-2 control-label">{{ _lang('Section') }}</label>
                                    <select name="section_id" id="section_id_select" class="form-control select2" data-selected="{{ request()->section_id }}">
                                        <option value="">{{ _lang('Select Class First') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="my-2 control-label">{{ _lang('From Roll No. (Optional)') }}</label>
                                    <input name="from_roll" type="number" class="form-control"
                                        value="{{ old('from_roll') }}">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="my-2 control-label">{{ _lang('To Roll No. (Optional)') }}</label>
                                    <input name="to_roll" type="number" class="form-control"
                                        value="{{ old('to_roll') }}">
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 row">
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary rect-btn">
                                    <i class="fa fa-search" aria-hidden="true"></i> {{ _lang('Find Students') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


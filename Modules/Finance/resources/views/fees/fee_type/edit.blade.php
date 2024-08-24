@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Fee Type') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">{{ _lang('Update Fee Type') }}</div>

                <div class="">
                    <form method="post" action="{{ action('FeeTypeController@update', $id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Fee Type') }}</label>
                                <input type="text" class="form-control" name="fee_type" value="{{ $feetype->fee_type }}"
                                    required>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Fee Code') }}</label>
                                <input type="text" class="form-control" name="fee_code" value="{{ $feetype->fee_code }}"
                                    required>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Note') }}</label>
                                <textarea class="form-control" name="note">{{ $feetype->note }}</textarea>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

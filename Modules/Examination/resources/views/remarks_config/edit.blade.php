@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Remarks Config') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Update Remark Config') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('remarks-config.update', $remarkConfig->id) }}" autocomplete="off"
                        enctype="multipart/form-data" method="post">
                        @csrf
                        @method('PATCH')


                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Remark Title') }}</label>
                                <input type="text" class="form-control" name="remark_title" required
                                    value="{{ $remarkConfig->remark_title }}" placeholder="Remark Title">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Remarks') }}</label>
                                <textarea type="text" class="form-control" name="remarks" placeholder="Remarks" cols="4" rows="4">{{ $remarkConfig->remarks }}</textarea>
                            </div>
                        </div>



                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

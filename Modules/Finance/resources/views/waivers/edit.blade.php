@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Waiver') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Update Waiver') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('waivers.update', $waiver->id) }}"method="post">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Waiver Name') }}</label>
                                <input type="text" class="form-control" name="waiver" value="{{ $waiver->waiver }}"
                                    required>
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

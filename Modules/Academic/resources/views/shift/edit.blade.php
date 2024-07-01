@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Shift') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Edit Shift') }}
                    </span>
                </div>
                <div class="">
                    <form action="{{ route('shift.update', $shift->id) }}"enctype="multipart/form-data" method="post">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Name') }}</label>
                                <input type="text" class="form-control" name="name" placeholder="Shift" required
                                    value="{{ $shift->name ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Edit Shift') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Academic/Session Year') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">

            <div class="card card-default">
                <div class="card-header">{{ _lang('Update Academic Year') }}</div>

                <div class="">
                    <form method="post" action="{{ route('academic-years.update', $id) }}">
                        @csrf
                        @method('PATCH')

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Session Name') }}</label>
                                <input type="text" class="form-control" name="session"
                                    value="{{ $academicYear->session }}" required>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Academic Year') }}</label>
                                <input type="text" class="form-control" name="year" value="{{ $academicYear->year }}"
                                    required>
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

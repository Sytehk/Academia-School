@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Exam') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Edit Exam') }}
                    </span>
                </div>
                <div class="">
                    <form action="{{ route('exams.update', $exam->id) }}"enctype="multipart/form-data" method="post">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Name') }}</label>
                                <input type="text" class="form-control" name="name" placeholder="Exam" required
                                    value="{{ $exam->name ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Exam Code') }}</label>
                                <input type="number" class="form-control" name="exam_code" placeholder="Exam Code"
                                    value="{{ $exam->exam_code ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Edit Exam') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

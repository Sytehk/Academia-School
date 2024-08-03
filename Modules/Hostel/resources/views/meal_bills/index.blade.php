@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Teachers') }}
@endsection

@section('create-button')
    <a href="{{ route('teachers.create') }}" class="btn btn-sm fw-bold btn-primary">{{ _lang('Add New Teacher') }}</a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <th>{{ _lang('SL No.') }}</th>
                        <th>{{ _lang('Profile') }}</th>
                        <th>{{ _lang('Name') }}</th>
                        <th>{{ _lang('Status') }}</th>
                        <th>{{ _lang('Action') }}</th>
                    </thead>
                    <tbody>
                        <tr>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

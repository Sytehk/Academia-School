@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Mark Input (Section Wise)') }}
@endsection
@section('content')
    <div class="row my-2 justify-content-center">
        <div class="col-lg-8">
            <div class="card-body border">
                <div class="card-header">
                    <h2>Class List</h2>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <tr>
                                <th>{{ _lang('Class') }}</th>
                                <th>{{ _lang('Section') }}</th>
                                <th>{{ _lang('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($classes as $class)
                                <tr>
                                    <td>{{ $class->class_name }}</td>
                                    <td>N/A</td>
                                    <td>
                                        <a href="{{ route('mark-input-section-wise-class', $class->id) }}"
                                            class="btn btn-warning btn-sm"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

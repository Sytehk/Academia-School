@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Student payment fee info') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default ">
                <div class="card-header">
                    <span class="card-title">{{ _lang('Paid Info') }}</s>
                </div>

            </div>

            @if (isset($student) && !empty($student))
                <div class="card-border">
                    <div class="card-header py-3 d-flex justify-content-between">
                        <div>
                            <p> Student Name: <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                            </p>
                        </div>

                        <div>
                            <p class=""> Roll : <strong>{{ $student->studentSession?->roll }}</strong></p>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                            <thead>
                                <th>{{ _lang('Student ID') }}</th>
                                <th>{{ _lang('Roll') }}</th>
                                <th>{{ _lang('Name') }}</th>
                                <th>{{ _lang('Paid Amount') }}</th>
                                <th>{{ _lang('Action') }}</th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-right">{{ $student->id }}</td>
                                    <td class="text-right">{{ $student->studentSession?->roll }}</td>
                                    <td class="text-right">{{ $student->first_name }} {{ $student->last_name }}</td>
                                    <td class="text-right">{{ $totalPaidAmount }}</td>
                                    <td><a href="{{ route('payment-details-info', $student->id) }}"><i class="fa fa-eye"
                                                aria-hidden="true">View</i></a></td>

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection

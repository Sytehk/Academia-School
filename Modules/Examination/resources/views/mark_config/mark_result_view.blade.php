@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Student Results') }}
@endsection
@section('content')
    <div class="row mt-5">
        <div class="col-lg-12">
            <div class="table-responsive">
                <div>
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <!--begin::Table head-->
                        <thead>
                            <!--begin::Table row-->
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th>{{ _lang('ID') }}</th>
                                <th>{{ _lang('Roll') }}</th>
                                <th>{{ _lang('Name') }}</th>
                                <th>{{ _lang('Subject') }}</th>
                                <th>{{ _lang('Exam') }}</th>
                                <th>{{ _lang('Total Marks') }}</th>
                                <th>{{ _lang('Grade Point') }}</th>
                                <th>{{ _lang('Grade') }}</th>
                            </tr>
                            <!--end::Table row-->
                        </thead>
                        <!--end::Table head-->
                        <!--begin::Table body-->
                        <tbody class="fw-semibold text-gray-600">
                            @foreach ($results as $result)
                                <tr>
                                    <td>{{ $result->student_id }}</td>
                                    <td>
                                        {{ $result->student->studentSession?->roll }}
                                    </td>
                                    <td>
                                        {{ $result->student->first_name }}
                                    </td>
                                    <td>{{ $result->subject?->subject_name }}</td>
                                    <td>{{ $result->exam?->name }}</td>
                                    <td>{{ $result->total_marks }}</td>
                                    <td>{{ $result->grade_point }}</td>
                                    <td>{{ $result->grade }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

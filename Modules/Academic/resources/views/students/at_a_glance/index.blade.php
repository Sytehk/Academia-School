@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('content')

    <div class="table-responsive">
        <table class="table align-middle table-row-dashed fs-6 gy-5">
            <thead>
                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                    <th colspan="3" class="text-left">
                        {{ _lang('Student List') }}
                    </th>
                    <th colspan="4" class="text-right">
                        {{ _lang('Total Found') }} :
                        {{ count($students) }}</th>
                </tr>
                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                    <th>{{ _lang('Student ID') }}</th>
                    <th>{{ _lang('Roll No.') }}</th>
                    <th>{{ _lang('Admission Number') }}</th>
                    <th>{{ _lang('Name') }}</th>
                    <th>{{ _lang('Class') }}</th>
                    <th>{{ _lang('Section') }}</th>
                    <th>{{ _lang('Gender') }}</th>
                    <th>{{ _lang('G.Mobile') }}</th>
                </tr>
            </thead>

            <tbody>
                @if (isset($students))
                    @foreach ($students as $student)
                        <tr>
                            <td>
                                {{ $student->id }}
                            </td>
                            <td>{{ $student->roll }}</td>
                            <td>{{ $student->qr_code }}</td>
                            <td>
                                @if ($student->user?->image != null)
                                    <img src="{{ $student->user?->image ? asset('storage/users/' . $student->user?->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                                        class="w-75px me-3" alt="">
                                @else
                                    <img src="{{ asset('images/no-image.png') }}" class="w-75px me-3">
                                @endif
                                {{ $student->first_name }} {{ $student->last_name }}
                            </td>
                            <td>{{ $student->class_name }}</td>
                            <td>
                                {{ $student->section_name }}
                            </td>
                            <td>
                                {{ $student->gender }}
                            </td>
                            <td>
                                {{ $student->phone }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-danger">No Student Found</td>
                    </tr>
                @endif

            </tbody>
        </table>
    </div>

@endsection

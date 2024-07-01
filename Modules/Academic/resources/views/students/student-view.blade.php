@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
<style>
    img {
        border-radius: 7px;
    }
</style>
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Student Profile') }}
                    </div>
                </div>

                <table class="table table-striped table-bordered" width="100%">
                    <tbody>
                        <tr>
                            @isset($student->user?->image)
                                <td class="text-center" colspan="4">
                                    @if ($student->user?->image != null)
                                        <img width="200px"
                                            src="{{ $student->user?->image ? asset('storage/users/' . $student->user?->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                                            class="me-3" alt="">
                                    @else
                                        <img width="200px" src="{{ asset('uploads/images/' . $student->user?->image) }}"
                                            class="me-3" alt="">
                                    @endif
                                </td>
                            @endisset
                        </tr>
                        <tr>
                            <td colspan="2">{{ _lang('Name') }}</td>
                            <td colspan="2">
                                @isset($student->first_name)
                                    {{ $student->first_name . ' ' . ($student->last_name ?? '') }}
                                @endisset
                            </td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Guardian') }}</td>
                            <td>
                                @isset($student->information_sent_to_name)
                                    {{ $student->information_sent_to_name }}
                                @endisset
                            </td>
                            <td>{{ _lang('Date Of Birth') }}</td>
                            <td>
                                @isset($student->birthday)
                                    {{ $student->birthday }}
                                @endisset
                            </td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Gender') }}</td>
                            <td>
                                @isset($student->gender)
                                    {{ $student->gender }}
                                @endisset
                            </td>
                            <td>{{ _lang('Blood Group') }}</td>
                            <td>
                                @isset($student->blood_group)
                                    {{ $student->blood_group }}
                                @endisset
                            </td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Religion') }}</td>
                            <td>
                                @isset($student->religion)
                                    {{ $student->religion }}
                                @endisset
                            </td>

                            <td>Address</td>
                            <td>
                                @isset($student->address)
                                    {{ $student->address }}
                                @endisset
                            </td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Phone') }}</td>
                            <td>
                                @isset($student->phone)
                                    {{ $student->phone }}
                                @endisset
                            </td>
                            <td>{{ _lang('Email') }}</td>
                            <td> @isset($student->user?->email)
                                    {{ $student->user?->email }}
                                @endisset
                            </td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Class') }}</td>
                            <td>
                                @isset($student->studentSession?->class?->class_name)
                                    {{ $student->studentSession?->class?->class_name }}
                                @endisset
                            </td>
                            <td>{{ _lang('Section') }}</td>
                            <td>
                                @isset($student->studentSession?->section?->section_name)
                                    {{ $student->studentSession?->section?->section_name }}
                                @endisset
                            </td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Register No') }}</td>
                            <td>
                                @isset($student->registration_no)
                                    {{ $student->registration_no }}
                                @endisset
                            </td>
                            <td>{{ _lang('Roll') }}</td>
                            <td>
                                @isset($student->studentSession?->roll)
                                    {{ $student->studentSession?->roll }}
                                @endisset
                            </td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Group') }}</td>
                            <td>
                                @isset($student->studentSession?->group)
                                    {{ $student->studentSession->group->group_name }}
                                @endisset
                            </td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Activities') }}</td>
                            <td>
                                @isset($student->activities)
                                    {{ $student->activities }}
                                @endisset
                            </td>
                            <td>{{ _lang('Remarks') }}</td>
                            <td>
                                @isset($student->remarks)
                                    {{ $student->remarks }}
                                @endisset
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

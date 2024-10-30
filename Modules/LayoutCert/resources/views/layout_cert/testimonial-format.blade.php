@extends('layouts.pdf.index')

@section('content-download')
    <div class="noprint print-download-buttons">
        @include('layouts.pdf.back-button')
        @include('layouts.pdf.download-button')
        @include('layouts.pdf.print-button')
    </div>
@endsection

@section('styles')
    @include('layoutcert::layout_cert.partials.style')
@endsection

@section('content')
    <div class="form-view">
        <div class="cert-border">
            <div class="cert-border-inner">
                @include('layouts.pdf.header')

                <h2 class="cert-title">{{ _lang('Testimonial') }}</h2>

                <div class="cert-body">
                    <p>
                        {{ _lang('This is to certify that') }}
                        <b>{{ $student->first_name }} {{ $student->last_name }}</b>,
                        {{ _lang('son/daughter of') }} <b>{{ $student->father_name ?: '-' }}</b>
                        {{ _lang('and') }} <b>{{ $student->mother_name ?: '-' }}</b>,
                        @if ($student->address)
                            {{ _lang('residing at') }} <b>{{ $student->address }}</b>,
                        @endif
                        {{ _lang('was/is a student of') }} <b>{{ get_option('school_name') }}</b>,
                        {{ _lang('bearing Roll No.') }} <b>{{ $studentSession->roll ?? '-' }}</b>
                        {{ _lang('in Class') }}
                        <b>{{ $studentSession->class?->class_name }}@if ($studentSession->section?->section_name)
                                - {{ $studentSession->section->section_name }}
                            @endif</b>
                        @if ($studentSession->group)
                            , <b>{{ $studentSession->group->group_name }}</b> {{ _lang('group') }}
                        @endif
                        @if ($studentSession->session)
                            {{ _lang('during the') }} <b>{{ $studentSession->session->session }}</b> {{ _lang('academic session') }}
                        @endif
                        @if ($student->date_of_admission)
                            , {{ _lang('having been admitted on') }}
                            <b>{{ \Carbon\Carbon::parse($student->date_of_admission)->format('d M Y') }}</b>
                        @endif
                        .
                    </p>
                    <p>
                        {{ _lang('During the period of study, the conduct and moral character of the student remained satisfactory, and no disciplinary action was ever taken.') }}
                    </p>
                    <p>{{ _lang('We wish him/her every success in future endeavors.') }}</p>
                </div>

                @include('layoutcert::layout_cert.partials.footer', ['marginTop' => '60px'])
            </div>
        </div>
    </div>
@endsection

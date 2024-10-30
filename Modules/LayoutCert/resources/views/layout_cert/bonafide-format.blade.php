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

                <h2 class="cert-title">{{ _lang('Bonafide Certificate') }}</h2>

                <div class="cert-body">
                    <p>
                        {{ _lang('This is to certify that') }}
                        <b>{{ $student->first_name }} {{ $student->last_name }}</b>
                        ({{ _lang('Roll') }} # {{ $studentSession->roll ?? '-' }}),
                        {{ _lang('son/daughter of') }}
                        <b>{{ $student->father_name ?: '-' }}</b> {{ _lang('and') }}
                        <b>{{ $student->mother_name ?: '-' }}</b>,
                        {{ _lang('is a bonafide student of') }} <b>{{ get_option('school_name') }}</b>,
                        {{ _lang('presently studying in Class') }}
                        <b>{{ $studentSession->class?->class_name }}@if ($studentSession->section?->section_name)
                                - {{ $studentSession->section->section_name }}
                            @endif</b>
                        @if ($studentSession->group)
                            , <b>{{ $studentSession->group->group_name }}</b> {{ _lang('group') }}
                        @endif
                        @if ($studentSession->session)
                            {{ _lang('for the academic session') }} <b>{{ $studentSession->session->session }}</b>
                        @endif
                        .
                    </p>
                    <p>{{ _lang('This certificate is issued on the request of the student/guardian for the purpose of bank account opening, visa application, scholarship, or any other legitimate purpose.') }}</p>
                </div>

                @include('layoutcert::layout_cert.partials.footer', ['marginTop' => '60px'])
            </div>
        </div>
    </div>
@endsection

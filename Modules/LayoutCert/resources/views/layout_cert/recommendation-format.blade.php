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

                <h2 class="cert-title">{{ _lang('To Whom It May Concern') }}</h2>

                <div class="cert-body">
                    <p>
                        {{ _lang('This is to certify that') }}
                        <b>{{ $student->first_name }} {{ $student->last_name }}</b>
                        ({{ _lang('Roll') }} # {{ $studentSession->roll ?? '-' }}),
                        {{ _lang('son/daughter of') }} <b>{{ $student->father_name ?: '-' }}</b>
                        {{ _lang('and') }} <b>{{ $student->mother_name ?: '-' }}</b>,
                        {{ _lang('is a student of') }} <b>{{ get_option('school_name') }}</b>
                        {{ _lang('in Class') }}
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
                    @if ($type === 'abroad-recommendation')
                        <p>
                            {{ _lang('The student is recommended for admission to an institution abroad. To the best of our knowledge, the student has not been involved in any anti-disciplinary or anti-institutional activity, and bears a good moral character.') }}
                        </p>
                    @else
                        <p>
                            {{ _lang('The student is recommended for higher secondary studies. To the best of our knowledge, the student has not been involved in any anti-disciplinary activity, and bears a good moral character.') }}
                        </p>
                    @endif
                    <p>{{ _lang('Any assistance given to the bearer will be highly appreciated.') }}</p>
                </div>

                @include('layoutcert::layout_cert.partials.footer', ['marginTop' => '60px'])
            </div>
        </div>
    </div>
@endsection

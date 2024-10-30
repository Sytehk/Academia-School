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

                <h2 class="cert-title">{{ _lang('Certificate of Attendance') }}</h2>

                <div class="cert-body">
                    <table style="width:100%; font-size: 16px; margin-bottom: 20px;">
                        <tr>
                            <td style="width:15%; padding: 6px 0;">{{ _lang('Name') }}</td>
                            <td style="width:35%; border-bottom: 1px dotted #000; font-weight:700;">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </td>
                            <td style="width:15%; padding: 6px 0;">{{ _lang('Roll') }}</td>
                            <td style="width:35%; border-bottom: 1px dotted #000; font-weight:700;">
                                {{ $studentSession->roll ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0;">{{ _lang('Class') }}</td>
                            <td style="border-bottom: 1px dotted #000; font-weight:700;">
                                {{ $studentSession->class?->class_name }}@if ($studentSession->section?->section_name)
                                    - {{ $studentSession->section->section_name }}
                                @endif
                            </td>
                            <td style="padding: 6px 0;">{{ _lang('Session') }}</td>
                            <td style="border-bottom: 1px dotted #000; font-weight:700;">
                                {{ $studentSession->session->session ?? '-' }}
                            </td>
                        </tr>
                    </table>
                    <p>
                        {{ _lang('This is to certify that the above-named student has maintained excellent attendance at') }}
                        <b>{{ get_option('school_name') }}</b>{{ _lang(', reflecting a consistent record of punctuality, discipline, and commitment to their studies.') }}
                    </p>
                    <p style="font-weight:700;">{{ _lang('Congratulations on this achievement.') }}</p>
                </div>

                @include('layoutcert::layout_cert.partials.footer', ['marginTop' => '50px'])
            </div>
        </div>
    </div>
@endsection

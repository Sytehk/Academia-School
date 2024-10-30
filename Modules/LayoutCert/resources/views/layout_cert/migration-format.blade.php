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

                <h2 class="cert-title">{{ _lang('Migration Certificate') }}</h2>

                <div class="cert-body">
                    <table style="width:100%; font-size: 15px; margin-bottom: 20px;">
                        <tr>
                            <td style="width:30%; padding: 5px 0;">{{ _lang('Student Name') }}</td>
                            <td style="width:70%; border-bottom: 1px dotted #000; font-weight:700;">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0;">{{ _lang("Father's Name") }}</td>
                            <td style="border-bottom: 1px dotted #000; font-weight:700;">{{ $student->father_name ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0;">{{ _lang("Mother's Name") }}</td>
                            <td style="border-bottom: 1px dotted #000; font-weight:700;">{{ $student->mother_name ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0;">{{ _lang('Registration No.') }}</td>
                            <td style="border-bottom: 1px dotted #000; font-weight:700;">{{ $student->registration_no ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0;">{{ _lang('Date of Birth') }}</td>
                            <td style="border-bottom: 1px dotted #000; font-weight:700;">
                                {{ $student->birthday ? \Carbon\Carbon::parse($student->birthday)->format('d M Y') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0;">{{ _lang('Last Class Studied') }}</td>
                            <td style="border-bottom: 1px dotted #000; font-weight:700;">
                                {{ $studentSession->class?->class_name }}@if ($studentSession->section?->section_name)
                                    - {{ $studentSession->section->section_name }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0;">{{ _lang('Roll No.') }}</td>
                            <td style="border-bottom: 1px dotted #000; font-weight:700;">{{ $studentSession->roll ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0;">{{ _lang('Academic Session') }}</td>
                            <td style="border-bottom: 1px dotted #000; font-weight:700;">
                                {{ $studentSession->session->session ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0;">{{ _lang('Date of Migration') }}</td>
                            <td style="border-bottom: 1px dotted #000; font-weight:700;">{{ now()->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px 0;">{{ _lang('Conduct') }}</td>
                            <td style="border-bottom: 1px dotted #000; font-weight:700;">{{ _lang('Good') }}</td>
                        </tr>
                    </table>
                    <p>
                        {{ _lang('This is to certify that the above particulars are correct according to the records of this institution, and that the student named above has no dues or objections pending. The student is hereby permitted to migrate to another Board/University/Institution.') }}
                    </p>
                </div>

                @include('layoutcert::layout_cert.partials.footer', ['marginTop' => '40px'])
            </div>
        </div>
    </div>
@endsection

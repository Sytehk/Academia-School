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

                <div class="cert-body">
                    {!! app(\Modules\LayoutCert\Services\CertificateService::class)->renderBody($template->body, $mergeFields ?? []) !!}
                </div>

                @include('layoutcert::layout_cert.partials.footer', ['marginTop' => '50px'])
            </div>
        </div>
    </div>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('QR Attendance') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-8 offset-md-2">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('My Attendance QR Code') }}
                    </div>
                </div>
                <div class="card-body text-center py-10">
                    @if ($qrCode)
                        <x-id-card.qr :value="$qrCode" :size="240" />
                        <p class="mt-5 text-muted">
                            {{ _lang('Show this code to your teacher or the attendance scanner to mark your attendance.') }}
                        </p>
                        @if ($className)
                            <p class="fw-bold">{{ $className }} @if ($sectionName) - {{ $sectionName }} @endif</p>
                        @endif
                    @else
                        <p class="text-danger">
                            {{ _lang('No QR code has been generated for your account yet. Please contact your school administration.') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

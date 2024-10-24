@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('System Settings') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">

                    @if (is_demo_mode())
                        <div class="alert alert-warning">
                            {{ _lang('General, email, and SMTP settings cannot be changed while demo mode is enabled.') }}
                        </div>
                    @endif

                    <ul class="nav nav-tabs setting-tab mb-4">

                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#general">
                                {{ _lang('General') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#email">
                                {{ _lang('Email') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#sms">
                                {{ _lang('SMS') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#socialMedia">
                                {{ _lang('Social Media') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#zoom">
                                {{ _lang('Zoom') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#googleMeet">
                                {{ _lang('Google Meet') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#appearance">
                                {{ _lang('Appearance') }}
                            </a>
                        </li>

                    </ul>

                    <div class="tab-content">

                        @include('systemconfiguration::general_settings.general')

                        @include('systemconfiguration::general_settings.email')

                        @include('systemconfiguration::general_settings.sms')

                        @include('systemconfiguration::general_settings.social_media')

                        @include('systemconfiguration::general_settings.zoom')

                        @include('systemconfiguration::general_settings.google_meet')

                        @include('systemconfiguration::general_settings.appearance')

                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

@section('js-script')
    <script>
        $(document).ready(function() {

            toggleSMTP();

            $(document).on('change', '#mail_type', function() {
                toggleSMTP();
            });

            function toggleSMTP() {
                if ($('#mail_type').val() === 'smtp') {
                    $('.smtp').prop('disabled', false);
                } else {
                    $('.smtp').prop('disabled', true);
                }
            }

            function toggleSmsGatewayFields() {
                var gateway = $('#sms_gateway').val();
                $('.gateway-fields').hide();
                $('.gateway-fields[data-gateway="' + gateway + '"]').show();
            }

            toggleSmsGatewayFields();
            $(document).on('change', '#sms_gateway', toggleSmsGatewayFields);
        });
    </script>
@endsection

@php
    use Carbon\Carbon;

    $today = Carbon::now()->format('d/m/Y');

    // Dynamic Settings
    $schoolName = get_option('school_name');
    $address = get_option('address');
    $phone = get_option('phone');
    $email = get_option('email');
    $website = get_option('website_link');
    $eiinCode = get_option('eiin_code');
    $instituteCode = get_option('institute_code');

    $logo = get_logo();
@endphp

{{-- =========================
    CERTIFICATE HEADER
========================= --}}
<header id="certificate-header" style="margin-bottom: 10px;">

    <table style="width:100%; border-collapse: collapse;">
        <tr>

            {{-- LEFT INFO --}}
            <td style="width:35%; vertical-align: top;">
                <div style="font-size: 13px; line-height: 22px;">

                    <div style="font-weight:700; font-size:16px;">
                        {{ $schoolName }}
                    </div>

                    @if ($address)
                        <div>
                            {{ $address }}
                        </div>
                    @endif

                    @if ($phone)
                        <div>
                            <strong>Phone:</strong> {{ $phone }}
                        </div>
                    @endif

                    @if ($email)
                        <div>
                            <strong>Email:</strong> {{ $email }}
                        </div>
                    @endif

                    @if ($website)
                        <div>
                            <strong>Website:</strong> {{ $website }}
                        </div>
                    @endif

                </div>
            </td>

            {{-- LOGO --}}
            <td style="width:30%; text-align:center; vertical-align: middle;">

                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo"
                        style="height: 90px; max-width:120px; object-fit:contain;">
                @endif

            </td>

            {{-- RIGHT INFO --}}
            <td style="width:35%; vertical-align: top; text-align:right;">

                <div style="font-size: 13px; line-height: 22px;">

                    @if ($eiinCode)
                        <div>
                            <strong>EIIN:</strong> {{ $eiinCode }}
                        </div>
                    @endif

                    @if ($instituteCode)
                        <div>
                            <strong>Institute Code:</strong> {{ $instituteCode }}
                        </div>
                    @endif

                    <div>
                        <strong>Date:</strong> {{ $today }}
                    </div>

                </div>

            </td>

        </tr>
    </table>

    {{-- HEADER BORDER --}}
    <div style="margin-top: 15px;">
        <hr style="border:0; border-top:3px solid #000; margin:0;">
        <hr style="border:0; border-top:1px solid #000; margin-top:3px;">
    </div>

</header>

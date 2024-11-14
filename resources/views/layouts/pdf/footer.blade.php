@php
    $principalName = get_option('principal_name') ?: 'Principal';
    $schoolName = get_option('school_name');
@endphp

{{-- =========================
    CERTIFICATE FOOTER
========================= --}}
<footer id="certificate-footer" style="
        width:100%;
        margin-top: {{ $marginTop ?? '80px' }};
    ">

    <table style="width:100%; border-collapse: collapse;">

        <tr>

            {{-- LEFT SIDE --}}
            <td style="width:50%; vertical-align: bottom;">

                <div style="font-size:13px; line-height:22px;">

                    @if (get_option('website_link'))
                        <div>
                            Website: {{ get_option('website_link') }}
                        </div>
                    @endif

                    @if (get_option('phone'))
                        <div>
                            Contact: {{ get_option('phone') }}
                        </div>
                    @endif

                </div>

            </td>

            {{-- RIGHT SIDE --}}
            <td style="width:50%; text-align:center; vertical-align: bottom;">

                <div>

                    {{-- Signature --}}
                    <div style="margin-bottom:10px;">
                        {!! printSignature() !!}
                    </div>

                    {{-- Signature Border --}}
                    <div
                        style="
                        border-top:1px solid #000;
                        width:220px;
                        margin-left:auto;
                        margin-right:auto;
                        padding-top:6px;
                    ">

                        <div style="font-weight:700;">
                            {{ $principalName }}
                        </div>

                        <div style="font-weight:600;">
                            Principal
                        </div>

                        <div style="font-size:13px;">
                            {{ $schoolName }}
                        </div>

                    </div>

                </div>

            </td>

        </tr>

    </table>

</footer>

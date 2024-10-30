@php
    $certSignatures = collect($template->signatures ?? []);
@endphp
<footer id="certificate-footer" style="width:100%; margin-top: {{ $marginTop ?? '40px' }};">
    <table style="width:100%; border-collapse: collapse;">
        <tr>
            <td style="width:34%; vertical-align: bottom; font-size:12px; line-height:20px;">
                @if (get_option('website_link'))
                    <div>{{ _lang('Website') }}: {{ get_option('website_link') }}</div>
                @endif
                @if (get_option('phone'))
                    <div>{{ _lang('Contact') }}: {{ get_option('phone') }}</div>
                @endif
                <div>{{ _lang('Issued') }}: {{ now()->format('d M Y') }}</div>
            </td>

            <td style="width:32%; text-align:center; vertical-align: bottom;">
                @include('layoutcert::layout_cert.partials.verification-block')
            </td>

            <td style="width:34%; vertical-align: bottom;">
                <table style="width:100%;">
                    <tr>
                        @if ($certSignatures->isEmpty())
                            <td style="text-align:center;">
                                <div style="margin-bottom:6px;">{!! printSignature() !!}</div>
                                <div style="border-top:1px solid #000; padding-top:6px; font-weight:700; font-size:12px;">
                                    {{ _lang('Principal') }}
                                </div>
                            </td>
                        @else
                            @foreach ($certSignatures as $sig)
                                <td style="text-align:center;">
                                    <div style="margin-bottom:6px;">{!! printSignature($sig['signature_id'] ?? null) !!}</div>
                                    <div style="border-top:1px solid #000; padding-top:6px; font-weight:700; font-size:12px;">
                                        {{ $sig['label'] ?? _lang('Signature') }}
                                    </div>
                                </td>
                            @endforeach
                        @endif
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</footer>

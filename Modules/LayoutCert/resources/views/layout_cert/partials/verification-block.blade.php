@if ($template->show_qr ?? true)
    <div class="cert-verify-block" style="text-align:center; font-size: 11px; color:#5b6b76;">
        {!! QrCode::size(64)->generate($verificationUrl) !!}
        <div style="margin-top:2px; letter-spacing:0.04em;">{{ $issue->verification_code }}</div>
        <div>{{ _lang('Scan to verify') }}</div>
    </div>
@endif

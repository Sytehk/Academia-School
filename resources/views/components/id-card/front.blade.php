@props([
    'school',           // Institution name
    'address' => null,  // Institution address (header sub-line)
    'logo' => null,     // Institution logo URL
    'label',            // e.g. "Student Identity Card"
    'photo' => null,    // Holder photo URL
    'name',             // Holder full name
    'role' => null,     // Class/Section, or Designation/Department
    'fields' => [],     // ['Roll' => '12', 'Blood' => 'B+', ...] — falsy values are dropped
    'qrValue' => null,  // Payload encoded in the QR
    'idLabel' => 'ID',  // Chip caption under the QR
    'idValue' => null,  // Chip value under the QR
    'validUntil' => null, // Pre-formatted validity date string
    'signature' => null,  // Principal signature image URL
])

@php
    // The ID leads the grid rather than sitting under the QR: the QR column is only
    // 15.5mm wide, which truncates real registration / employee numbers.
    $rows = collect($idValue ? [$idLabel => $idValue] : [])
        ->merge($fields)
        ->map(fn ($v) => is_string($v) ? trim($v) : $v)
        // Only keep rows that carry data — a card full of "-" looks unfinished.
        ->reject(fn ($v) => $v === null || $v === '' || $v === false)
        ->take(6);
@endphp

<div class="idc-card idc-front">
    <div class="idc-head">
        @if ($logo)
            <img class="idc-logo" src="{{ $logo }}" alt="">
        @endif
        <div class="idc-head-text">
            <div class="idc-school-name">{{ $school }}</div>
            @if ($address)
                <div class="idc-school-address">{{ $address }}</div>
            @endif
            <div class="idc-card-label">{{ $label }}</div>
        </div>
    </div>

    <div class="idc-body">
        <div class="idc-photo-col">
            <img class="idc-photo" src="{{ $photo ?: asset('images/no-image.png') }}" alt="">
        </div>

        <div class="idc-identity">
            <div class="idc-name">{{ $name }}</div>
            @if ($role)
                <div class="idc-role">{{ $role }}</div>
            @endif

            @if ($rows->isNotEmpty())
                <div class="idc-rule"></div>

                <div class="idc-fields">
                    @foreach ($rows as $fieldLabel => $fieldValue)
                        <div class="idc-field-label">{{ _lang($fieldLabel) }}</div>
                        <div @class(['idc-field-value', 'is-primary' => $loop->first && $idValue])>{{ $fieldValue }}</div>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($qrValue)
            <div class="idc-qr-col">
                <div class="idc-qr-box">
                    <x-id-card.qr :value="$qrValue" />
                </div>
                <div class="idc-qr-caption">{{ _lang('Scan to verify') }}</div>
            </div>
        @endif
    </div>

    <div class="idc-foot">
        <div class="idc-validity">
            @if ($validUntil)
                <span class="idc-validity-label">{{ _lang('Valid Until') }}</span>
                <b>{{ $validUntil }}</b>
            @endif
        </div>

        <div class="idc-sign-block">
            @if ($signature)
                <img class="idc-signature" src="{{ $signature }}" alt="">
            @else
                <div class="idc-sign-spacer"></div>
            @endif
            <div class="idc-sign-label">{{ _lang('Principal') }}</div>
        </div>
    </div>
</div>

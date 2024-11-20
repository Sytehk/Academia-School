@props([
    'school',
    'logo' => null,
    'address' => null,
    'phone' => null,
    'email' => null,
    'website' => null,
    'emergency' => null,   // ['label' => 'Guardian', 'value' => '017...'] or a plain string
    'qrValue' => null,
    'rules' => [],         // Terms & conditions lines; a sensible default set is used when empty
    'note' => null,        // Extra institution-specific note
])

@php
    $rules = ! empty($rules)
        ? $rules
        : [
            _lang('This card is the property of the institution and must be carried at all times on campus.'),
            _lang('Report loss or damage to the school office immediately.'),
            _lang('Non-transferable. Misuse may lead to disciplinary action.'),
            _lang('If found, please return to the address printed below.'),
        ];

    $emergencyLabel = is_array($emergency) ? ($emergency['label'] ?? _lang('Emergency Contact')) : _lang('Emergency Contact');
    $emergencyValue = is_array($emergency) ? ($emergency['value'] ?? null) : $emergency;
@endphp

<div class="idc-card idc-back">
    <div class="idc-back-strip">
        @if ($logo)
            <img class="idc-back-logo" src="{{ $logo }}" alt="">
        @endif
        <div class="idc-back-title">{{ $school }}</div>
    </div>

    <div class="idc-back-body">
        <div class="idc-back-main">
            <p class="idc-back-heading">{{ _lang('Terms & Conditions') }}</p>
            <ul class="idc-rules">
                @foreach ($rules as $rule)
                    <li><span>{{ $rule }}</span></li>
                @endforeach
            </ul>

            @if ($emergencyValue)
                <div class="idc-emergency">
                    <b>{{ $emergencyLabel }}</b>
                    {{ $emergencyValue }}
                </div>
            @elseif ($note)
                <div class="idc-emergency">
                    <b>{{ _lang('Note') }}</b>
                    {{ $note }}
                </div>
            @endif
        </div>

        @if ($qrValue)
            <div class="idc-back-aside">
                <div class="idc-qr-box">
                    <x-id-card.qr :value="$qrValue" />
                </div>
                <div class="idc-qr-caption">{{ _lang('Scan to verify') }}</div>
            </div>
        @endif
    </div>

    <div class="idc-back-foot">
        @if ($address)
            <b>{{ $address }}</b>
        @endif
        @if ($phone)
            {{ _lang('Phone') }}: {{ $phone }}
        @endif
        @if ($email)
            &middot; {{ $email }}
        @endif
        @if ($website)
            <br>{{ $website }}
        @endif
    </div>
</div>

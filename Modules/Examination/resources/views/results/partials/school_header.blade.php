@php
    $header = $header ?? ($sheet['header'] ?? []);
    $settings = $settings ?? [];
    $theme = $header['theme'] ?? [];
@endphp
<table class="school-header">
    <tr>
        <td class="school-meta">
            <div class="school-name">{{ $header['school_name'] ?? get_option('school_name') }}</div>
            @if (!empty($header['address']) || get_option('address'))
                <div class="school-tagline">{{ $header['address'] ?? get_option('address') }}</div>
            @endif
            @if (!empty($header['phone']) || get_option('phone'))
                <div>{{ _lang('Phone') }}: {{ $header['phone'] ?? get_option('phone') }}</div>
            @endif
            @if (!empty($header['email']) || get_option('email'))
                <div>{{ _lang('Email') }}: {{ $header['email'] ?? get_option('email') }}</div>
            @endif
        </td>
        <td class="school-logo">
            @php $logo = $header['logo'] ?? get_logo(); @endphp
            @if ($logo)
                <img src="{{ $logo }}" alt="logo">
            @endif
        </td>
        <td class="school-meta right">
            @if (!empty($header['eiin_code']) || get_option('eiin_code'))
                <div>{{ _lang('EIIN') }}: {{ $header['eiin_code'] ?? get_option('eiin_code') }}</div>
            @endif
            @if (!empty($header['institute_code']) || get_option('institute_code'))
                <div>{{ _lang('Institute Code') }}: {{ $header['institute_code'] ?? get_option('institute_code') }}</div>
            @endif
            @if (!empty($header['website']) || get_option('website_link'))
                <div>{{ $header['website'] ?? get_option('website_link') }}</div>
            @endif
            <div>{{ _lang('Date') }}: {{ now()->format('d/m/Y') }}</div>
        </td>
    </tr>
</table>

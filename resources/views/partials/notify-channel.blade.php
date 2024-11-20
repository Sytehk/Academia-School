@php
    $selectedChannel = old('notify_channel', $notifyChannel ?? 'none');
@endphp
<div class="form-group {{ $wrapperClass ?? '' }}">
    <label class="{{ $labelClass ?? 'form-label' }}" for="{{ $fieldId ?? 'notify_channel' }}">
        {{ _lang($label ?? 'Send Notification') }}
    </label>
    <select name="notify_channel" id="{{ $fieldId ?? 'notify_channel' }}"
        class="{{ $selectClass ?? 'form-select' }}">
        @unless (!empty($hideNone))
            <option value="none" @selected($selectedChannel === 'none')>{{ _lang('Do not send') }}</option>
        @endunless
        <option value="sms" @selected($selectedChannel === 'sms')>{{ _lang('SMS') }}</option>
        <option value="whatsapp" @selected($selectedChannel === 'whatsapp')>{{ _lang('WhatsApp') }}</option>
        <option value="both" @selected($selectedChannel === 'both')>{{ _lang('SMS + WhatsApp') }}</option>
    </select>
    <input type="hidden" name="sms_status" value="0">
</div>

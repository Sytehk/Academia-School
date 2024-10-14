<div class="col-lg-6">
    <div class="form-group">
        <label class="control-label my-2">{{ _lang('SMS Template') }}</label>
        <select name="" id="sms-template" class="form-select form-select-sm form-select-solid {{ $type }}"
            data-control="select2">
            <option value="">{{ _lang('--Select--') }}</option>
            @foreach ($smsTemplates as $item)
                <option data-template="{{ $item->description }}" value="{{ $item->id }}">
                    {{ _lang($item->name) }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="col-lg-12">
    <div class="form-group">
        <label class="control-label my-2">{{ _lang('Message') }} ( {{ _lang('MAX 300') }})</label>
        <textarea id="message_content_{{ $type }}" class="form-control" name="body">{{ old('body') }}</textarea>
    </div>
</div>

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ $template->exists ? _lang('Edit Certificate Template') : _lang('New Certificate Template') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">
                        {{ $template->exists ? _lang('Edit Template') : _lang('New Template') }}
                    </span>
                </div>
                <div class="card-body">
                    <form
                        action="{{ $template->exists ? route('certificate-templates.update', $template->id) : route('certificate-templates.store') }}"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        @if ($template->exists)
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Template Name') }}</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $template->name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Certificate Type') }}</label>
                                <select name="type" class="form-select" required>
                                    @foreach ($types as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ old('type', $template->type) === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Orientation') }}</label>
                                <select name="orientation" class="form-select">
                                    <option value="portrait"
                                        {{ old('orientation', $template->orientation) === 'portrait' ? 'selected' : '' }}>
                                        {{ _lang('Portrait') }}</option>
                                    <option value="landscape"
                                        {{ old('orientation', $template->orientation) === 'landscape' ? 'selected' : '' }}>
                                        {{ _lang('Landscape') }}</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Font Family') }}</label>
                                <input type="text" name="font_family" class="form-control"
                                    value="{{ old('font_family', $template->font_family) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Primary Color') }}</label>
                                <input type="color" name="primary_color" class="form-control form-control-color"
                                    value="{{ old('primary_color', $template->primary_color) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Secondary Color') }}</label>
                                <input type="color" name="secondary_color" class="form-control form-control-color"
                                    value="{{ old('secondary_color', $template->secondary_color) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Background Image') }}</label>
                                <input type="file" name="background_image" class="form-control" accept="image/*">
                                @if ($template->background_image)
                                    <div class="form-text">{{ _lang('Current') }}: {{ $template->background_image }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Watermark Text') }}</label>
                                <input type="text" name="watermark_text" class="form-control"
                                    placeholder="{{ _lang('Defaults to school name') }}"
                                    value="{{ old('watermark_text', $template->watermark_text) }}">
                            </div>

                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="show_watermark" value="1"
                                        id="show_watermark" {{ old('show_watermark', $template->show_watermark) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_watermark">{{ _lang('Watermark') }}</label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="show_qr" value="1"
                                        id="show_qr" {{ old('show_qr', $template->show_qr) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_qr">{{ _lang('QR Verification') }}</label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="show_logo" value="1"
                                        id="show_logo" {{ old('show_logo', $template->show_logo) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_logo">{{ _lang('School Logo') }}</label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        id="is_active" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">{{ _lang('Active') }}</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <hr>
                                <label class="form-label d-block">{{ _lang('Signatures') }}</label>
                                <div class="form-text mb-2">
                                    {{ _lang('Leave empty to use a single Principal signature (default behavior).') }}
                                </div>
                                @php
                                    $existingSignatures = old('signatures', $template->signatures ?? []);
                                @endphp
                                <div id="signature-rows">
                                    @forelse ($existingSignatures as $i => $sig)
                                        <div class="row g-2 mb-2 signature-row">
                                            <div class="col-md-5">
                                                <input type="text" name="signatures[{{ $i }}][label]"
                                                    class="form-control" placeholder="{{ _lang('Label e.g. Principal') }}"
                                                    value="{{ $sig['label'] ?? '' }}">
                                            </div>
                                            <div class="col-md-5">
                                                <select name="signatures[{{ $i }}][signature_id]" class="form-select">
                                                    <option value="">{{ _lang('Select Signature') }}</option>
                                                    @foreach ($signatures as $s)
                                                        <option value="{{ $s->id }}"
                                                            {{ ($sig['signature_id'] ?? null) == $s->id ? 'selected' : '' }}>
                                                            {{ $s->title }} ({{ $s->place_at }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger w-100"
                                                    onclick="this.closest('.signature-row').remove()">
                                                    {{ _lang('Remove') }}
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                    @endforelse
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-signature-row">
                                    <i class="fa fa-plus"></i> {{ _lang('Add Signature Row') }}
                                </button>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                {{ $template->exists ? _lang('Update Template') : _lang('Create Template') }}
                            </button>
                            <a href="{{ route('certificate-templates.index') }}"
                                class="btn btn-outline-secondary">{{ _lang('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        document.getElementById('add-signature-row').addEventListener('click', function() {
            const container = document.getElementById('signature-rows');
            const index = container.querySelectorAll('.signature-row').length;
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 signature-row';
            row.innerHTML = `
                <div class="col-md-5">
                    <input type="text" name="signatures[${index}][label]" class="form-control" placeholder="{{ _lang('Label e.g. Principal') }}">
                </div>
                <div class="col-md-5">
                    <select name="signatures[${index}][signature_id]" class="form-select">
                        <option value="">{{ _lang('Select Signature') }}</option>
                        @foreach ($signatures as $s)
                            <option value="{{ $s->id }}">{{ $s->title }} ({{ $s->place_at }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.signature-row').remove()">{{ _lang('Remove') }}</button>
                </div>
            `;
            container.appendChild(row);
        });
    </script>
@endsection

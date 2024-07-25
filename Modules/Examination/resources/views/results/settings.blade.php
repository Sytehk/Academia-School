@extends('layouts.backend')
@section('page_title_breadcrumb')
    @php($settings = (isset($card_settings) && is_array($card_settings) ? $card_settings : []))
    {{ _lang('Result Card Settings') }}
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('result-card-settings.update') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label">{{ _lang('Result Title') }}</label>
                    <input type="text" name="result_title" class="form-control" value="{{ $row->result_title ?? '' }}">
                </div>
                <div class="row">
                    @foreach ([
                        'show_photo' => 'Show photo',
                        'show_position' => 'Show position',
                        'show_gpa' => 'Show GPA',
                        'show_percentage' => 'Show percentage',
                        'show_failed_subjects' => 'Show failed subjects',
                        'show_attendance' => 'Show attendance',
                        'show_remarks' => 'Show remarks',
                        'show_cognitive' => 'Show cognitive domain',
                        'show_affective' => 'Show affective domain',
                        'show_psychomotor' => 'Show psychomotor domain',
                        'show_teacher_signature' => 'Show teacher signature',
                        'show_principal_signature' => 'Show principal signature',
                        'show_parent_signature' => 'Show parent signature',
                    ] as $key => $label)
                        <div class="col-md-4 mb-3">
                            <label class="form-check form-check-custom">
                                <input type="checkbox" class="form-check-input" name="{{ $key }}" value="1" @checked($settings[$key] ?? false)>
                                <span class="form-check-label ms-2">{{ _lang($label) }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ _lang('Class Teacher label') }}</label>
                        <input class="form-control" name="signature_class_teacher" value="{{ $settings['signature_labels']['class_teacher'] ?? '' }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ _lang('Principal label') }}</label>
                        <input class="form-control" name="signature_principal" value="{{ $settings['signature_labels']['principal'] ?? '' }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ _lang('Parent label') }}</label>
                        <input class="form-control" name="signature_parent" value="{{ $settings['signature_labels']['parent'] ?? '' }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ _lang('Class Teacher Signature') }}</label>
                        <select name="teacher_signature_id" class="form-select">
                            <option value="">{{ _lang('Auto-detect from signatures table') }}</option>
                            @foreach ($signatures as $signature)
                                <option value="{{ $signature->id }}" @selected(($settings['teacher_signature_id'] ?? '') == $signature->id)>
                                    {{ $signature->title }} ({{ $signature->place_at }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ _lang('Principal Signature') }}</label>
                        <select name="principal_signature_id" class="form-select">
                            <option value="">{{ _lang('Auto-detect from signatures table') }}</option>
                            @foreach ($signatures as $signature)
                                <option value="{{ $signature->id }}" @selected(($settings['principal_signature_id'] ?? '') == $signature->id)>
                                    {{ $signature->title }} ({{ $signature->place_at }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ _lang('Failed student ranking') }}</label>
                        <select name="rank_failed_students" class="form-select">
                            <option value="with_others" @selected(($settings['rank_failed_students'] ?? '') === 'with_others')>{{ _lang('Rank with others') }}</option>
                            <option value="after_passers" @selected(($settings['rank_failed_students'] ?? '') === 'after_passers')>{{ _lang('After passers') }}</option>
                            <option value="unranked" @selected(($settings['rank_failed_students'] ?? '') === 'unranked')>{{ _lang('Unranked') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ _lang('Report card template') }}</label>
                        <select name="template" class="form-select">
                            <option value="standard" @selected(($settings['template'] ?? 'standard') === 'standard')>{{ _lang('Standard (GPA)') }}</option>
                            <option value="nigerian" @selected(($settings['template'] ?? '') === 'nigerian')>{{ _lang('Nigerian (CA + Exam, positions, cumulative)') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ _lang('Primary Color') }}</label>
                        <input type="color" class="form-control form-control-color" name="primary_color" value="{{ $settings['primary_color'] ?? '#1a365d' }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ _lang('Accent Color') }}</label>
                        <input type="color" class="form-control form-control-color" name="accent_color" value="{{ $settings['accent_color'] ?? '#c9a227' }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ _lang('Watermark Opacity') }} (0–1)</label>
                        <input type="number" step="0.01" min="0" max="1" class="form-control" name="watermark_opacity" value="{{ $settings['watermark_opacity'] ?? 0.08 }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-check form-check-custom mt-8">
                            <input type="checkbox" class="form-check-input" name="show_watermark" value="1" @checked($settings['show_watermark'] ?? true)>
                            <span class="form-check-label ms-2">{{ _lang('Show institute banner watermark') }}</span>
                        </label>
                    </div>
                </div>
                <button class="btn btn-primary">{{ _lang('Save') }}</button>
            </form>
        </div>
    </div>
@endsection

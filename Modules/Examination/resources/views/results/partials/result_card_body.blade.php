@php
    $result = $result ?? [];
    // The global view composer overwrites $settings on every view; the card's own settings travel as $card_settings.
    $settings = (isset($card_settings) && is_array($card_settings) ? $card_settings : []);
    $header = $header ?? [];
    $signatures = $header['signatures'] ?? [];
    $studentSession = $result['student_session'] ?? null;
@endphp
<div class="result-card">
    @include('examination::results.partials.school_header', ['header' => $header])
    <div class="doc-title">
        {{ $result_title ?? _lang('Academic Result Card') }}
        @if(!empty($exam_name)) — {{ $exam_name }} @endif
    </div>

    <table class="info-table">
        <tr>
            <td><strong>{{ _lang('Student Name') }}:</strong> {{ $result['student_name'] ?? '' }}</td>
            <td><strong>{{ _lang('Student ID') }}:</strong> {{ $result['registration_no'] ?? $result['student_id'] ?? '' }}</td>
            <td rowspan="4" style="width:96px; text-align:center; vertical-align:middle;">
                @if (($settings['show_photo'] ?? true) && !empty($result['student_image']))
                    <img class="photo" src="{{ asset('storage/'.$result['student_image']) }}" alt="photo">
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>{{ _lang('Roll') }}:</strong> {{ $result['roll'] ?? '' }}</td>
            <td><strong>{{ _lang('Class') }}:</strong> {{ $result['class_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>{{ _lang('Section') }}:</strong> {{ $result['section_name'] ?? '' }}</td>
            <td><strong>{{ _lang('Group') }}:</strong> {{ $result['group_name'] ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>{{ _lang('Session') }}:</strong> {{ $result['session_name'] ?? '' }}</td>
            <td><strong>{{ _lang('Date of Birth') }}:</strong> {{ $result['birthday'] ?? '-' }}</td>
        </tr>
    </table>

    <table class="marks-table">
        <thead>
            <tr>
                <th>{{ _lang('Subject') }}</th>
                <th>{{ _lang('Full Marks') }}</th>
                <th>{{ _lang('Pass Marks') }}</th>
                <th>{{ _lang('Obtained') }}</th>
                <th>{{ _lang('Grade') }}</th>
                <th>{{ _lang('Grade Point') }}</th>
                <th>{{ _lang('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($result['subjects'] ?? []) as $subject)
                <tr>
                    <td class="left">{{ $subject['subject_name'] }}</td>
                    <td>{{ number_format((float) $subject['full_marks'], 0) }}</td>
                    <td>{{ number_format((float) $subject['pass_marks'], 0) }}</td>
                    <td>{{ $subject['obtained'] === null ? '-' : number_format((float) $subject['obtained'], 2) }}</td>
                    <td>{{ $subject['grade'] ?? '-' }}</td>
                    <td>{{ $subject['grade_point'] === null ? '-' : number_format((float) $subject['grade_point'], 2) }}</td>
                    <td class="{{ in_array($subject['status'] ?? '', ['fail', 'absent'], true) ? 'fail' : (($subject['status'] ?? '') === 'pass' ? 'pass' : '') }}">
                        {{ strtoupper($subject['status'] ?? '-') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td>{{ _lang('Total') }}: <span class="accent">{{ number_format((float) ($result['total_marks'] ?? 0), 2) }}</span> / {{ number_format((float) ($result['total_full_marks'] ?? 0), 2) }}</td>
            @if ($settings['show_percentage'] ?? true)
                <td>{{ _lang('Percentage') }}: <span class="accent">{{ number_format((float) ($result['percentage'] ?? 0), 2) }}%</span></td>
            @endif
            @if ($settings['show_gpa'] ?? true)
                <td>{{ _lang('GPA') }}: <span class="accent">{{ number_format((float) ($result['gpa'] ?? 0), 2) }}</span> ({{ $result['grade'] ?? '-' }})</td>
            @endif
            @if ($settings['show_position'] ?? true)
                <td>{{ _lang('Position') }}: {{ $result['position'] ?? '-' }}</td>
            @endif
        </tr>
        <tr>
            <td colspan="2">{{ _lang('Result') }}: <strong class="{{ ($result['status'] ?? '') === 'pass' ? 'pass' : 'fail' }}">{{ strtoupper($result['status'] ?? '-') }}</strong></td>
            @if ($settings['show_failed_subjects'] ?? true)
                <td colspan="2">{{ _lang('Failed Subjects') }}: {{ !empty($result['failed_subjects']) ? implode(', ', $result['failed_subjects']) : _lang('None') }}</td>
            @endif
        </tr>
        @if (($settings['show_attendance'] ?? true) && ($result['attendance_total'] ?? 0) > 0)
            <tr>
                <td colspan="4">{{ _lang('Exam Attendance') }}: {{ $result['attendance_present'] ?? 0 }} / {{ $result['attendance_total'] ?? 0 }}</td>
            </tr>
        @endif
        @if (($settings['show_remarks'] ?? true) && !empty($result['teacher_remarks']))
            <tr>
                <td colspan="4">{{ _lang('Teacher Remarks') }}: {{ $result['teacher_remarks'] }}</td>
            </tr>
        @endif
    </table>

    @include('examination::results.partials.domains', ['domains' => $domains ?? $result['domains'] ?? [], 'settings' => $settings])

    <table class="sign-table signatures">
        <tr>
            @if ($settings['show_teacher_signature'] ?? true)
                <td>
                    @if (!empty($signatures['class_teacher']['image']))
                        <img class="signature-img" src="{{ $signatures['class_teacher']['image'] }}" alt="">
                    @endif
                    <div class="signature-line">{{ $settings['signature_labels']['class_teacher'] ?? _lang('Class Teacher') }}</div>
                    @if (!empty($signatures['class_teacher']['title']))
                        <div class="muted">{{ $signatures['class_teacher']['title'] }}</div>
                    @endif
                </td>
            @endif
            @if ($settings['show_principal_signature'] ?? true)
                <td>
                    @if (!empty($signatures['principal']['image']))
                        <img class="signature-img" src="{{ $signatures['principal']['image'] }}" alt="">
                    @endif
                    <div class="signature-line">{{ $settings['signature_labels']['principal'] ?? _lang('Head Teacher / Principal') }}</div>
                    @if (!empty($signatures['principal']['title']))
                        <div class="muted">{{ $signatures['principal']['title'] }}</div>
                    @endif
                </td>
            @endif
            @if ($settings['show_parent_signature'] ?? true)
                <td>
                    <div style="height:48px;"></div>
                    <div class="signature-line">{{ $settings['signature_labels']['parent'] ?? _lang('Parent / Guardian') }}</div>
                </td>
            @endif
        </tr>
    </table>

    <div class="result-footer">
        {{ _lang('This is a computer-generated result card and is valid with official institute seal and signature.') }}
    </div>
</div>

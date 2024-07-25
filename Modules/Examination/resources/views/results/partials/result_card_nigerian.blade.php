{{--
    Nigerian-style terminal report card: CA / exam component columns, subject
    total, class highest / lowest / average, subject position, grade and remark;
    attendance for the term; overall average, class average and position out of
    the class; cumulative term columns on later terms; affective & psychomotor
    domains; class-teacher and principal comments; next-term resumption & fees.
--}}
@php
    $result = $result ?? [];
    $settings = (isset($card_settings) && is_array($card_settings) ? $card_settings : []);
    $header = $header ?? [];
    $signatures = $header['signatures'] ?? [];
    $subjects = $result['subjects'] ?? [];
    $componentTitles = collect($subjects)->flatMap(fn ($s) => collect($s['components'] ?? [])->pluck('title'))->unique()->values();
    $cumulative = ($settings['show_cumulative'] ?? true) ? ($result['cumulative'] ?? null) : null;
    $cumulativeBySubject = collect($cumulative['subjects'] ?? [])->keyBy('subject_id');
    $priorTerms = $cumulative ? array_slice($cumulative['terms'], 0, -1) : [];
    $attendance = $result['term_attendance'] ?? null;
    $term = $result['term'] ?? null;
    $stats = $result['class_statistics'] ?? [];
    $comments = $result['comments'] ?? [];
    $ordinal = function ($n) {
        if ($n === null || $n === '') return '-';
        $n = (int) $n;
        $suffix = ($n % 100 >= 11 && $n % 100 <= 13) ? 'th' : (['th', 'st', 'nd', 'rd'][$n % 10] ?? 'th');
        return $n.$suffix;
    };
@endphp
<div class="result-card">
    @include('examination::results.partials.school_header', ['header' => $header])
    <div class="doc-title">
        {{ $result_title ?? _lang('Terminal Report Sheet') }}
        @if(!empty($exam_name)) — {{ $exam_name }} @endif
    </div>

    <table class="info-table">
        <tr>
            <td><strong>{{ _lang('Name') }}:</strong> {{ $result['student_name'] ?? '' }}</td>
            <td><strong>{{ _lang('Admission No') }}:</strong> {{ $result['admission_no'] ?? $result['registration_no'] ?? '-' }}</td>
            <td rowspan="4" style="width:96px; text-align:center; vertical-align:middle;">
                @if (($settings['show_photo'] ?? true) && !empty($result['student_image']))
                    <img class="photo" src="{{ asset('storage/'.$result['student_image']) }}" alt="photo">
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>{{ _lang('Class') }}:</strong> {{ $result['class_name'] ?? '' }} {{ $result['section_name'] ?? '' }}</td>
            <td><strong>{{ _lang('Session') }}:</strong> {{ $result['session_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>{{ _lang('No. in Class') }}:</strong> {{ $result['students_in_class'] ?? '-' }}</td>
            <td>
                @if ($settings['show_position'] ?? true)
                    <strong>{{ _lang('Position in Class') }}:</strong> {{ $ordinal($result['position'] ?? null) }}
                @endif
            </td>
        </tr>
        <tr>
            @if ($attendance && ($settings['show_attendance'] ?? true))
                <td><strong>{{ _lang('Times School Opened') }}:</strong> {{ $attendance['days_school_opened'] }}</td>
                <td><strong>{{ _lang('Times Present') }}:</strong> {{ $attendance['days_present'] }} &nbsp; <strong>{{ _lang('Absent') }}:</strong> {{ $attendance['days_absent'] }}</td>
            @else
                <td colspan="2"><strong>{{ _lang('Roll') }}:</strong> {{ $result['roll'] ?? '' }}</td>
            @endif
        </tr>
    </table>

    <table class="marks-table">
        <thead>
            <tr>
                <th>{{ _lang('Subject') }}</th>
                @foreach ($componentTitles as $title)
                    <th>{{ $title }}</th>
                @endforeach
                <th>{{ _lang('Total') }}</th>
                @foreach ($priorTerms as $priorTerm)
                    <th>{{ $priorTerm['name'] }}</th>
                @endforeach
                @if ($cumulative)
                    <th>{{ _lang('Cum. Avg') }}</th>
                @endif
                @if ($settings['show_class_statistics'] ?? true)
                    <th>{{ _lang('Class Highest') }}</th>
                    <th>{{ _lang('Class Lowest') }}</th>
                    <th>{{ _lang('Class Avg') }}</th>
                @endif
                @if ($settings['show_subject_position'] ?? true)
                    <th>{{ _lang('Pos.') }}</th>
                @endif
                <th>{{ _lang('Grade') }}</th>
                <th>{{ _lang('Remark') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($subjects as $subject)
                @php
                    $componentValues = collect($subject['components'] ?? [])->keyBy('title');
                    $cum = $cumulativeBySubject->get($subject['subject_id']);
                @endphp
                <tr>
                    <td class="left">{{ $subject['subject_name'] }}</td>
                    @foreach ($componentTitles as $title)
                        @php $c = $componentValues->get($title); @endphp
                        <td>{{ $c && $c['obtained'] !== null ? rtrim(rtrim(number_format((float) $c['obtained'], 2), '0'), '.') : '-' }}</td>
                    @endforeach
                    <td><strong>{{ $subject['obtained'] === null ? '-' : rtrim(rtrim(number_format((float) $subject['obtained'], 2), '0'), '.') }}</strong></td>
                    @foreach ($priorTerms as $i => $priorTerm)
                        @php $t = $cum['terms'][$i]['total'] ?? null; @endphp
                        <td>{{ $t === null ? '-' : rtrim(rtrim(number_format((float) $t, 2), '0'), '.') }}</td>
                    @endforeach
                    @if ($cumulative)
                        <td>{{ ($cum['cumulative_average'] ?? null) === null ? '-' : number_format((float) $cum['cumulative_average'], 1) }}</td>
                    @endif
                    @if ($settings['show_class_statistics'] ?? true)
                        <td>{{ $subject['class_highest'] ?? '-' }}</td>
                        <td>{{ $subject['class_lowest'] ?? '-' }}</td>
                        <td>{{ $subject['class_average'] === null ? '-' : number_format((float) $subject['class_average'], 1) }}</td>
                    @endif
                    @if ($settings['show_subject_position'] ?? true)
                        <td>{{ $ordinal($subject['subject_position'] ?? null) }}</td>
                    @endif
                    <td>{{ $subject['grade'] ?? '-' }}</td>
                    <td class="left">{{ $subject['remarks'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td>{{ _lang('Total Score') }}: <span class="accent">{{ rtrim(rtrim(number_format((float) ($result['total_marks'] ?? 0), 2), '0'), '.') }}</span> / {{ rtrim(rtrim(number_format((float) ($result['total_full_marks'] ?? 0), 2), '0'), '.') }}</td>
            <td>{{ _lang('Average') }}: <span class="accent">{{ number_format((float) ($result['percentage'] ?? 0), 2) }}%</span></td>
            @if ($settings['show_class_statistics'] ?? true)
                <td>{{ _lang('Class Average') }}: {{ ($stats['class_average'] ?? null) === null ? '-' : number_format((float) $stats['class_average'], 2).'%' }}</td>
            @endif
            <td>{{ _lang('Grade') }}: <strong>{{ $result['grade'] ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td colspan="2">{{ _lang('Result') }}: <strong class="{{ ($result['status'] ?? '') === 'pass' ? 'pass' : 'fail' }}">{{ strtoupper($result['status'] ?? '-') }}</strong></td>
            @if ($cumulative && ($cumulative['overall_average'] ?? null) !== null)
                <td colspan="2">{{ _lang('Cumulative Average') }}: {{ number_format((float) $cumulative['overall_average'], 2) }}</td>
            @elseif ($settings['show_failed_subjects'] ?? true)
                <td colspan="2">{{ _lang('Failed Subjects') }}: {{ !empty($result['failed_subjects']) ? implode(', ', $result['failed_subjects']) : _lang('None') }}</td>
            @endif
        </tr>
    </table>

    @include('examination::results.partials.domains', ['domains' => $domains ?? $result['domains'] ?? [], 'settings' => $settings])

    <table class="summary-table">
        @if ($settings['show_remarks'] ?? true)
            <tr>
                <td style="width:30%"><strong>{{ _lang("Class Teacher's Comment") }}</strong></td>
                <td>{{ $comments['teacher_comment'] ?? $result['teacher_remarks'] ?? '' }}</td>
            </tr>
            <tr>
                <td><strong>{{ _lang("Principal's / Head Teacher's Comment") }}</strong></td>
                <td>{{ $comments['principal_comment'] ?? '' }}</td>
            </tr>
        @endif
        @if (($settings['show_next_term'] ?? true) && $term && ($term['next_term_begins'] || $term['next_term_fee'] !== null))
            <tr>
                <td><strong>{{ _lang('Next Term Begins') }}</strong></td>
                <td>
                    {{ $term['next_term_begins'] ? \Carbon\Carbon::parse($term['next_term_begins'])->format('l, jS F Y') : '-' }}
                    @if ($term['next_term_fee'] !== null)
                        &nbsp;|&nbsp; <strong>{{ _lang('Next Term Fees') }}:</strong> {{ number_format((float) $term['next_term_fee'], 2) }}
                    @endif
                    @if (!empty($term['next_term_note']))
                        <div class="muted">{{ $term['next_term_note'] }}</div>
                    @endif
                </td>
            </tr>
        @endif
    </table>

    <table class="sign-table signatures">
        <tr>
            @if ($settings['show_teacher_signature'] ?? true)
                <td>
                    @if (!empty($signatures['class_teacher']['image']))
                        <img class="signature-img" src="{{ $signatures['class_teacher']['image'] }}" alt="">
                    @endif
                    <div class="signature-line">{{ $settings['signature_labels']['class_teacher'] ?? _lang('Class Teacher') }}</div>
                </td>
            @endif
            @if ($settings['show_principal_signature'] ?? true)
                <td>
                    @if (!empty($signatures['principal']['image']))
                        <img class="signature-img" src="{{ $signatures['principal']['image'] }}" alt="">
                    @endif
                    <div class="signature-line">{{ $settings['signature_labels']['principal'] ?? _lang('Principal') }}</div>
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

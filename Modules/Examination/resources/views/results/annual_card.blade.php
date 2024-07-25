@extends('examination::results.print_layout')

@section('content')
    @php
        $studentSession = $student_session ?? ($grand['student_session'] ?? null);
        $settings = (isset($card_settings) && is_array($card_settings) ? $card_settings : []);
        $header = $header ?? [];
        $signatures = $header['signatures'] ?? [];
    @endphp
    <div class="result-card">
        @include('examination::results.partials.school_header', ['header' => $header])
        <div class="doc-title">{{ $result_title ?? _lang('Annual / All Term Result Card') }}</div>

        <table class="info-table">
            <tr>
                <td><strong>{{ _lang('Student Name') }}:</strong> {{ trim(($studentSession->student->first_name ?? '') . ' ' . ($studentSession->student->last_name ?? '')) }}</td>
                <td><strong>{{ _lang('Student ID') }}:</strong> {{ $studentSession->student?->registration_no ?? '' }}</td>
            </tr>
            <tr>
                <td><strong>{{ _lang('Roll') }}:</strong> {{ $studentSession->roll ?? '' }}</td>
                <td><strong>{{ _lang('Class') }}:</strong> {{ $studentSession->class?->class_name }}</td>
            </tr>
            <tr>
                <td><strong>{{ _lang('Section') }}:</strong> {{ $studentSession->section?->section_name }}</td>
                <td><strong>{{ _lang('Group') }}:</strong> {{ $studentSession->group?->group_name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>{{ _lang('Session') }}:</strong> {{ $studentSession->session?->session }}</td>
                <td><strong>{{ _lang('Card Type') }}:</strong> {{ _lang('All Term / Final') }}</td>
            </tr>
        </table>

        <table class="marks-table">
            <thead>
                <tr>
                    <th>{{ _lang('Subject') }}</th>
                    @foreach ($terms as $term)
                        <th>{{ $term->exam?->name }}</th>
                    @endforeach
                    <th>{{ _lang('Average') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subject_comparison as $row)
                    <tr>
                        <td class="left">{{ $row['subject_name'] }}</td>
                        @foreach ($row['terms'] as $termMark)
                            <td>{{ $termMark['obtained'] === null ? '-' : number_format((float) $termMark['obtained'], 2) }}</td>
                        @endforeach
                        <td><strong>{{ $row['average'] === null ? '-' : number_format((float) $row['average'], 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if (!empty($grand['configured']))
            <table class="summary-table">
                <tr>
                    @if (($grand['calculation_method'] ?? '') === 'weighted_percentage')
                        <td>{{ _lang('Final Score') }}: <span class="accent">{{ number_format((float) ($grand['final_score'] ?? 0), 2) }}%</span></td>
                    @else
                        <td>{{ _lang('Final Marks') }}: <span class="accent">{{ number_format((float) ($grand['final_obtained'] ?? 0), 2) }}</span> / {{ number_format((float) ($grand['final_full_marks'] ?? 0), 2) }}</td>
                    @endif
                    <td>{{ _lang('Grade') }}: {{ $grand['grade_name'] ?? '-' }}</td>
                    @if ($settings['show_gpa'] ?? true)
                        <td>{{ _lang('GPA') }}: {{ number_format((float) ($grand['gpa'] ?? 0), 2) }}</td>
                    @endif
                    @if ($settings['show_position'] ?? true)
                        <td>{{ _lang('Position') }}: {{ $grand['position'] ?? '-' }}</td>
                    @endif
                </tr>
                <tr>
                    <td colspan="4">{{ _lang('Result') }}: <strong class="{{ ($grand['status'] ?? '') === 'pass' ? 'pass' : 'fail' }}">{{ strtoupper($grand['status'] ?? '-') }}</strong></td>
                </tr>
            </table>
        @endif

        @include('examination::results.partials.domains', ['domains' => $domains ?? [], 'settings' => $settings])

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
                        <div class="signature-line">{{ $settings['signature_labels']['principal'] ?? _lang('Head Teacher / Principal') }}</div>
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
            {{ _lang('This is an official annual result card issued by the institute.') }}
        </div>
    </div>
@endsection

@extends('examination::results.print_layout')

@section('content')
    @php $result = $result ?? []; @endphp
    <div class="result-card">
        @include('examination::results.partials.school_header', ['header' => $header ?? []])
        <div class="doc-title">{{ _lang('Complete Mark Sheet') }} — {{ $exam_name ?? '' }}</div>
        <table class="info-table">
            <tr>
                <td><strong>{{ _lang('Student') }}:</strong> {{ $result['student_name'] ?? '' }}</td>
                <td><strong>{{ _lang('Roll') }}:</strong> {{ $result['roll'] ?? '' }}</td>
                <td><strong>{{ _lang('Class') }}:</strong> {{ $result['class_name'] ?? '' }} / {{ $result['section_name'] ?? '' }}</td>
            </tr>
        </table>
        <table class="marks-table">
            <thead>
                <tr>
                    <th>{{ _lang('Subject') }}</th>
                    <th>{{ _lang('Components') }}</th>
                    <th>{{ _lang('Total') }}</th>
                    <th>{{ _lang('Grade') }}</th>
                    <th>{{ _lang('GP') }}</th>
                    <th>{{ _lang('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach (($result['subjects'] ?? []) as $subject)
                    <tr>
                        <td class="left">{{ $subject['subject_name'] }}</td>
                        <td class="left">
                            @forelse ($subject['components'] as $component)
                                {{ $component['title'] }}: {{ $component['obtained'] ?? '-' }}
                                @if (!$loop->last) | @endif
                            @empty
                                -
                            @endforelse
                        </td>
                        <td>{{ $subject['obtained'] ?? '-' }}</td>
                        <td>{{ $subject['grade'] ?? '-' }}</td>
                        <td>{{ $subject['grade_point'] ?? '-' }}</td>
                        <td>{{ strtoupper($subject['status'] ?? '-') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table class="summary-table">
            <tr>
                <td>{{ _lang('Total') }}: {{ $result['total_marks'] ?? 0 }}</td>
                <td>{{ _lang('GPA') }}: {{ $result['gpa'] ?? 0 }}</td>
                <td>{{ _lang('Grade') }}: {{ $result['grade'] ?? '-' }}</td>
                <td>{{ _lang('Position') }}: {{ $result['position'] ?? '-' }}</td>
            </tr>
        </table>
        <div class="toolbar" style="text-align:left; margin-top:10px;">
            <a href="{{ route('exam-result-card', request()->query()) }}">{{ _lang('Print Result Card') }}</a>
            |
            <a href="{{ route('student-mark-pdf', request()->query()) }}">{{ _lang('PDF') }}</a>
        </div>
    </div>
@endsection

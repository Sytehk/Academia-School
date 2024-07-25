<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ _lang('Grand Final Result') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .toolbar {
            text-align: right;
            margin-bottom: 15px;
        }

        .result-card {
            border: 2px solid #333;
            padding: 15px;
            margin-bottom: 20px;
            page-break-after: always;
        }

        .result-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .result-header h2 {
            margin: 0 0 4px 0;
        }

        .result-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .student-info-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .student-info-table td {
            padding: 3px 6px;
            font-size: 14px;
        }

        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .breakdown-table th,
        .breakdown-table td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: center;
            font-size: 14px;
        }

        .breakdown-table th {
            background: #f0f0f0;
        }

        .final-summary {
            width: 100%;
            border-collapse: collapse;
        }

        .final-summary td {
            border: 1px solid #333;
            padding: 8px;
            font-size: 15px;
            font-weight: bold;
            text-align: center;
        }

        .not-configured {
            padding: 20px;
            text-align: center;
            color: #a30000;
            font-weight: bold;
        }

        @media print {
            .toolbar {
                display: none;
            }

            .result-card {
                border: 2px solid #333;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <button onclick="window.print()">{{ _lang('Print') }}</button>
    </div>

    @foreach ($results as $result)
        @php $studentSession = $result['student_session']; @endphp
        <div class="result-card">
            <div class="result-header">
                <h2>{{ get_option('school_name') }}</h2>
                <h4>{{ _lang('Grand Final Result') }}</h4>
            </div>

            <table class="student-info-table">
                <tr>
                    <td><strong>{{ _lang('Name') }}:</strong>
                        {{ trim(($studentSession->student->first_name ?? '') . ' ' . ($studentSession->student->last_name ?? '')) }}
                    </td>
                    <td><strong>{{ _lang('Roll') }}:</strong> {{ $studentSession->roll }}</td>
                </tr>
                <tr>
                    <td><strong>{{ _lang('Class') }}:</strong> {{ $studentSession->class?->class_name }}</td>
                    <td><strong>{{ _lang('Section') }}:</strong> {{ $studentSession->section?->section_name }}</td>
                </tr>
                <tr>
                    <td><strong>{{ _lang('Group') }}:</strong> {{ $studentSession->group?->group_name }}</td>
                    <td><strong>{{ _lang('Session') }}:</strong> {{ $studentSession->session?->session }}</td>
                </tr>
            </table>

            @if (! $result['configured'])
                <div class="not-configured">
                    {{ _lang('Grand Final Result is not configured for this class yet.') }}
                </div>
            @else
                <table class="breakdown-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Exam') }}</th>
                            <th>{{ _lang('Obtained') }}</th>
                            <th>{{ _lang('Full Marks') }}</th>
                            <th>{{ _lang('Percentage') }}</th>
                            @if ($result['calculation_method'] === 'weighted_percentage')
                                <th>{{ _lang('Weight') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($result['breakdown'] as $row)
                            <tr>
                                <td>{{ $row['exam_name'] }}</td>
                                <td>{{ number_format($row['obtained'], 2) }}</td>
                                <td>{{ number_format($row['full_marks'], 2) }}</td>
                                <td>{{ number_format($row['percentage'], 2) }}%</td>
                                @if ($result['calculation_method'] === 'weighted_percentage')
                                    <td>{{ number_format($row['weight'], 2) }}%</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <table class="final-summary">
                    <tr>
                        @if ($result['calculation_method'] === 'weighted_percentage')
                            <td>{{ _lang('Final Score') }}: {{ number_format($result['final_score'], 2) }}%</td>
                        @else
                            <td>{{ _lang('Final Marks') }}: {{ number_format($result['final_obtained'], 2) }} /
                                {{ number_format($result['final_full_marks'], 2) }}</td>
                        @endif
                        <td>{{ _lang('Grade') }}: {{ $result['grade_name'] }}</td>
                        <td>{{ _lang('Position') }}: {{ $result['position'] ?? '-' }}</td>
                    </tr>
                </table>
            @endif
        </div>
    @endforeach
</body>

</html>

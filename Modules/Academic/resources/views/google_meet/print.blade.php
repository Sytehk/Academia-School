<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $meeting->title }}</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 6px 10px; border: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
    <h2>{{ get_option('school_name') }}</h2>
    <h3>{{ $meeting->title }}</h3>

    <table>
        <tr><th>{{ _lang('Teacher') }}</th><td>{{ $meeting->teacher?->name ?? '-' }}</td></tr>
        <tr><th>{{ _lang('Class') }}</th><td>{{ $meeting->classModel?->class_name ?? '-' }}</td></tr>
        <tr><th>{{ _lang('Section') }}</th><td>{{ $meeting->section?->section_name ?? '-' }}</td></tr>
        <tr><th>{{ _lang('Subject') }}</th><td>{{ $meeting->subject?->subject_name ?? '-' }}</td></tr>
        <tr><th>{{ _lang('Date') }}</th><td>{{ optional($meeting->start_date)->format('d M Y') }}</td></tr>
        <tr><th>{{ _lang('Time') }}</th><td>{{ $meeting->start_time }} - {{ $meeting->end_time }}</td></tr>
        <tr><th>{{ _lang('Duration') }}</th><td>{{ $meeting->duration }} {{ _lang('minutes') }}</td></tr>
        <tr><th>{{ _lang('Status') }}</th><td>{{ $meeting->computedStatus() }}</td></tr>
        <tr><th>{{ _lang('Meet Link') }}</th><td>{{ $meeting->meet_link ?? '-' }}</td></tr>
        <tr><th>{{ _lang('Description') }}</th><td>{{ $meeting->description ?? '-' }}</td></tr>
        <tr><th>{{ _lang('Notes') }}</th><td>{{ $meeting->notes ?? '-' }}</td></tr>
    </table>

    <script>window.print();</script>
</body>
</html>

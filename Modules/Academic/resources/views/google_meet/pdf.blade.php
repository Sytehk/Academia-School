<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h3>{{ get_option('school_name') }} - Google Meet Meetings</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Teacher</th>
                <th>Class</th>
                <th>Section</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($meetings as $meeting)
                <tr>
                    <td>{{ $meeting->id }}</td>
                    <td>{{ $meeting->title }}</td>
                    <td>{{ $meeting->teacher?->name ?? '-' }}</td>
                    <td>{{ $meeting->classModel?->class_name ?? '-' }}</td>
                    <td>{{ $meeting->section?->section_name ?? '-' }}</td>
                    <td>{{ optional($meeting->start_date)->format('d M Y') }}</td>
                    <td>{{ $meeting->start_time }} - {{ $meeting->end_time }}</td>
                    <td>{{ $meeting->computedStatus() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

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
    <h3>{{ get_option('school_name') }} - WhatsApp Delivery Logs</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Phone</th>
                <th>Message</th>
                <th>Status</th>
                <th>Retries</th>
                <th>Sent At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $log->student?->user?->name ?? '-' }}</td>
                    <td>{{ $log->phone }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($log->message, 80) }}</td>
                    <td>{{ ucfirst($log->status) }}</td>
                    <td>{{ $log->retry_count }}</td>
                    <td>{{ $log->created_at?->format('d-m-Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

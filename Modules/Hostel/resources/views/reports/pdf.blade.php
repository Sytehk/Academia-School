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
    <h3>{{ get_option('school_name') }} - Hostel Collection Report</h3>
    <table>
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Student</th>
                <th>Room</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($collections as $collection)
                <tr>
                    <td>{{ $collection->invoice_id }}</td>
                    <td>{{ $collection->student?->user?->name ?? '-' }}</td>
                    <td>{{ $collection->roomMember?->room?->room_number ?? '-' }}</td>
                    <td>{{ $collection->total_paid }}</td>
                    <td>{{ $collection->total_due }}</td>
                    <td>{{ $collection->invoice_date }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Phone Book</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; padding: 16px; }
        h1 { font-size: 16px; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 5px 7px; text-align: left; }
        th { background: #1a365d; color: #fff; }
        tbody tr:nth-child(even) td { background: #f7f9fc; }
        @page { margin: 12mm; }
    </style>
</head>

<body>
    <h1>{{ _lang('Phone Book') }} — {{ get_option('school_name') }}</h1>
    <table>
        <thead>
            <tr>
                <th>{{ _lang('Name') }}</th>
                <th>{{ _lang('Phone') }}</th>
                <th>{{ _lang('Category') }}</th>
                <th>{{ _lang('Class') }}</th>
                <th>{{ _lang('Section') }}</th>
                <th>{{ _lang('Note') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($phoneBooks as $phoneBook)
                <tr>
                    <td>{{ $phoneBook->name }}</td>
                    <td>{{ $phoneBook->phone }}</td>
                    <td>{{ $phoneBook->category?->name }}</td>
                    <td>{{ $phoneBook->class?->class_name }}</td>
                    <td>{{ $phoneBook->section?->section_name }}</td>
                    <td>{{ $phoneBook->note }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">{{ _lang('No contacts found for the selected filters.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>

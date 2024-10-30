<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ _lang('Certificate Verification') }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #eef2f0;
            margin: 0;
            padding: 40px 16px;
            color: #16211e;
        }

        .verify-card {
            max-width: 480px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(20, 30, 27, 0.15);
            padding: 32px 28px;
            text-align: center;
        }

        .verify-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px;
            color: #fff;
        }

        .verify-icon.ok {
            background: #1e8e5a;
        }

        .verify-icon.fail {
            background: #b3261e;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 6px;
        }

        p.sub {
            color: #5b6b76;
            margin: 0 0 24px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        table td {
            padding: 8px 0;
            border-bottom: 1px solid #eef2f0;
        }

        table td:first-child {
            color: #5b6b76;
            width: 40%;
        }

        table td:last-child {
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="verify-card">
        @if ($issue)
            <div class="verify-icon ok">&#10003;</div>
            <h1>{{ _lang('Certificate Verified') }}</h1>
            <p class="sub">{{ _lang('This certificate was genuinely issued by the institute.') }}</p>
            <table>
                <tr>
                    <td>{{ _lang('Certificate Type') }}</td>
                    <td>{{ $typeLabel }}</td>
                </tr>
                <tr>
                    <td>{{ _lang('Issued To') }}</td>
                    <td>{{ $issue->subject_name }}</td>
                </tr>
                <tr>
                    <td>{{ _lang('Issued On') }}</td>
                    <td>{{ $issue->issued_at?->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td>{{ _lang('Verification Code') }}</td>
                    <td>{{ $issue->verification_code }}</td>
                </tr>
            </table>
        @else
            <div class="verify-icon fail">&#10007;</div>
            <h1>{{ _lang('Certificate Not Found') }}</h1>
            <p class="sub">{{ _lang('This verification code does not match any issued certificate. It may be invalid or the certificate may not have been issued through this system.') }}</p>
        @endif
    </div>
</body>

</html>

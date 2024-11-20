<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>New Support Ticket Created</title>
</head>

<body style="margin:0; padding:0; font-family:Arial, sans-serif; background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f4f4f4">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" bgcolor="#ffffff"
                    style="margin:40px 0; border-radius:8px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#FF5722; padding:20px; text-align:center; color:#ffffff;">
                            <h1 style="margin:0; font-size:24px;">🛠 New Support Ticket Created</h1>
                        </td>
                    </tr>

                    <!-- Greeting -->
                    <tr>
                        <td style="padding:30px;">
                            <p style="font-size:16px; color:#333333;">
                                Hi <strong>{{ $ticket->name ?? 'User' }}</strong>,
                            </p>
                            <p style="font-size:16px; color:#333333;">
                                Your support ticket has been successfully created. Here are the details:
                            </p>
                        </td>
                    </tr>

                    <!-- Ticket Details -->
                    <tr>
                        <td style="padding:0 30px 30px 30px;">
                            <h2
                                style="font-size:18px; color:#FF5722; border-bottom:2px solid #FF5722; padding-bottom:5px;">
                                📌 Ticket Details</h2>
                            <table width="100%" cellpadding="5" cellspacing="0"
                                style="font-size:14px; color:#333333; border-collapse:collapse;">
                                <tr>
                                    <td style="width:150px; font-weight:bold;">Title:</td>
                                    <td>{{ $ticket->title }}</td>
                                </tr>
                                <tr>
                                    <td style="width:150px; font-weight:bold;">Description:</td>
                                    <td>{{ $ticket->description }}</td>
                                </tr>
                                <tr>
                                    <td style="width:150px; font-weight:bold;">Category:</td>
                                    <td>{{ $ticket->category->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="width:150px; font-weight:bold;">Related URL:</td>
                                    <td>{{ $ticket->related_url ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="width:150px; font-weight:bold;">Support Plan:</td>
                                    <td>{{ $ticket->support_plan ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- CTA Button -->
                    <tr>
                        <td style="padding:20px 30px; text-align:center;">
                            <a href="{{ url('/support-tickets/' . $ticket->id) }}"
                                style="display:inline-block; padding:15px 30px; background-color:#FF5722; color:#ffffff; text-decoration:none; font-size:16px; border-radius:5px;">
                                View Ticket
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="padding:20px 30px; font-size:14px; color:#777777; text-align:center; background-color:#f4f4f4;">
                            Thanks,<br>
                            <strong>{{ config('app.name') }} Team</strong>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>

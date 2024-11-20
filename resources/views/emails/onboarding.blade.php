<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Welcome to {{ config('app.name') }}</title>
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
                            <h1 style="margin:0; font-size:28px;">🎉 Welcome to {{ config('app.name') }}!</h1>
                        </td>
                    </tr>

                    <!-- Greeting -->
                    <tr>
                        <td style="padding:30px;">
                            <p style="font-size:16px; color:#333;">Hi <strong>{{ $instituteName }}</strong>,</p>
                            <p style="font-size:16px; color:#333;">
                                Your institute has been successfully onboarded to
                                <strong>{{ config('app.name') }}</strong>.<br>
                                Below are your login details and trial package information:
                            </p>
                        </td>
                    </tr>

                    <!-- Account Details -->
                    <tr>
                        <td style="padding:0 30px 20px;">
                            <h2
                                style="font-size:18px; color:#FF5722; border-bottom:2px solid #FF5722; padding-bottom:5px;">
                                🆔 Account Details
                            </h2>

                            <ul style="list-style:none; padding:0; margin:10px 0; color:#333;">
                                <li><strong>Institute ID:</strong> {{ $instituteId }}</li>
                                <li><strong>Email:</strong> {{ $email }}</li>
                                <li><strong>Password:</strong> {{ $password }}</li>
                            </ul>

                            <p style="font-size:14px; color:#FF5722;">
                                ⚠️ Please change your password after first login.
                            </p>
                        </td>
                    </tr>

                    <!-- Trial Package -->
                    <tr>
                        <td style="padding:0 30px 20px;">
                            <h2
                                style="font-size:18px; color:#FF5722; border-bottom:2px solid #FF5722; padding-bottom:5px;">
                                📦 Trial Package Details
                            </h2>

                            <ul style="list-style:none; padding:0; margin:10px 0; color:#333;">
                                <li><strong>Package Name:</strong> {{ $packageName }}</li>
                                <li><strong>Trial Duration:</strong> {{ $trialDays }} days</li>
                                <li><strong>Valid Until:</strong> {{ $trialEndDate }}</li>
                            </ul>
                        </td>
                    </tr>

                    <!-- Dashboard CTA -->
                    <tr>
                        <td style="padding:30px; text-align:center;">
                            <a href="{{ $loginUrl }}"
                                style="display:inline-block; padding:15px 30px; background-color:#FF5722;
                               color:#ffffff; text-decoration:none; font-size:16px; border-radius:5px;">
                                Login to Dashboard
                            </a>
                        </td>
                    </tr>

                    <!-- NameServer Info -->
                    <tr>
                        <td style="padding:20px 30px;">
                            <h2
                                style="font-size:18px; color:#FF5722; border-bottom:2px solid #FF5722; padding-bottom:5px;">
                                🌐 DNS / NameServer Configuration
                            </h2>

                            <p style="font-size:15px; color:#333; margin:10px 0;">
                                Please update your domain’s NameServers to point your subdomain automatically:
                            </p>

                            <ul style="list-style:none; padding:0; margin:0; color:#333;">
                                <li><strong>NS1:</strong> ns1.vercel-dns.com</li>
                                <li><strong>NS2:</strong> ns2.vercel-dns.com</li>
                            </ul>

                            <p style="font-size:13px; color:#555; margin-top:10px;">
                                After updating NameServers, propagation may take up to 30 minutes.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="padding:20px 30px; font-size:14px; color:#777; text-align:center; background-color:#f4f4f4;">
                            For any support, contact us:<br>
                            <a href="mailto:support@yourdomain.com" style="color:#FF5722; text-decoration:none;">
                                support@yourdomain.com
                            </a>
                            <br><br>
                            — The {{ config('app.name') }} Team
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>

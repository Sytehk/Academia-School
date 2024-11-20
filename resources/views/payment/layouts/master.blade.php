<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ 'Payment' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-5/bootstrap.min.css') }}">
    @stack('script')

    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: #f4f4f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .loader-container {
            text-align: center;
        }

        .loader {
            border: 8px solid #e0e0e0;
            border-top: 8px solid #4A90E2;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        h1 {
            color: #333;
            font-size: 1.5rem;
        }
    </style>
</head>

<body>
    @yield('content')
    <script src="{{ asset('assets/bootstrap-5/bootstrap.bundle.min.js') }}"></script>
</body>

</html>

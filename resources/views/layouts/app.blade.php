<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sikasir-4SR')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="@yield('body_class', 'pos-body')">
    @yield('content')

    <script src="{{ asset('assets/js/pos.js') }}"></script>
</body>
</html>

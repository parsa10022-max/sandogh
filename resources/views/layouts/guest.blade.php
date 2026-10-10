<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'صندوق قرض الحسنه')
    </title>

    {{-- Bootstrap RTL --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >

    {{-- Bootstrap Icons --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    {{-- فونت محلی وزیرمتن --}}
    <link
        href="{{ asset('auth.css') }}"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f5f7fb;
            min-height: 100vh;
        }
    </style>

    @stack('styles')
</head>

<body>

    @yield('content')

    @stack('scripts')

</body>

</html>

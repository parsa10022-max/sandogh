<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])

    @stack('styles')
</head>

<body>

<div class="container py-4">
    @yield('content')
</div>

@stack('scripts')

</body>
</html>

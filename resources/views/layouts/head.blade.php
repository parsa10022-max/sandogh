<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<meta
    name="csrf-token"
    content="{{ csrf_token() }}"
>

<title>
    @yield('title', config('app.name'))
</title>

<meta
    name="description"
    content="سیستم مدیریت صندوق قرض الحسنه"
>

<meta
    name="author"
    content="{{ config('app.name') }}"
>

@vite([
'resources/css/app.css',
'resources/js/app.js'
])

@stack('styles')

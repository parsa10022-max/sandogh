<!DOCTYPE html>

<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $title ?? 'رسید صندوق' }}
    </title>

    @vite([
    'resources/css/app.css',
    'resources/css/receipts.css',
    'resources/js/app.js',
    ])
    ```

</head>

<body class="receipt-body">

```
<main class="receipt-page">

    <div class="receipt">

        {{-- Header --}}
        @include('receipts.partials.header')

        <hr>

        {{-- محتوای رسید --}}
        @yield('receipt-content')

        <hr>

        {{-- Footer --}}
        @include('receipts.partials.footer')

    </div>

    <div class="receipt-actions d-print-none">

        <button
            type="button"
            class="btn btn-success"
            onclick="window.print()"
        >
            <i class="bi bi-printer"></i>
            چاپ رسید
        </button>

        <a
            href="{{ url('/customer/dashboard') }}"
            class="btn btn-secondary"
        >
            <i class="bi bi-arrow-right"></i>
            بازگشت
        </a>

    </div>

</main>


</body>

</html>

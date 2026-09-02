<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#5b3df5">

    <title>
        @yield('title', 'پنل مشتری')
    </title>

    @vite([
    'resources/css/app.css',
    'resources/css/customer-layout.css',

    'resources/js/app.js',
    'resources/js/customer.js',
    ])

    <link rel="manifest" href="/manifest.json">

    @stack('styles')

</head>

<body class="customer-panel">

<div class="customer-layout">

    {{-- =====================================================
         SIDEBAR
         ===================================================== --}}

    @php
        $unreadNotificationsCount = \App\Models\Notification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();
    @endphp
    @include('customer.layouts.sidebar')


    {{-- =====================================================
         MAIN
         ===================================================== --}}
    <div class="customer-main">


        {{-- =================================================
             HEADER
             ================================================= --}}
        @include('customer.layouts.header')


        {{-- =================================================
             CONTENT
             ================================================= --}}
        <main class="customer-content">

            @yield('content')

        </main>

    </div>

</div>


{{-- =========================================================
     MOBILE BOTTOM NAVIGATION
     ========================================================= --}}
<nav class="customer-bottom-nav">


    <a href="{{ url('/customer/dashboard') }}"
       class="customer-bottom-item active">

        <i class="bi bi-house-fill"></i>

        <span>
            خانه
        </span>

    </a>


    <a href="#"
       class="customer-bottom-item">

        <i class="bi bi-wallet2"></i>

        <span>
            حساب‌ها
        </span>

    </a>


    <a href="#"
       class="customer-bottom-item">

        <i class="bi bi-cash-coin"></i>

        <span>
            وام‌ها
        </span>

    </a>


    <a href="#"
       class="customer-bottom-item">

        <i class="bi bi-grid"></i>

        <span>
            خدمات
        </span>

    </a>


    <a href="#"
       class="customer-bottom-item">

        <i class="bi bi-person"></i>

        <span>
            پروفایل
        </span>

    </a>

</nav>


@stack('scripts')

</body>
</html>

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
    <aside class="customer-sidebar">

        {{-- Logo --}}
        <div class="customer-sidebar-brand">

            <div class="customer-brand-logo">
                <i class="bi bi-bank2"></i>
            </div>

            <strong>صندوق</strong>

            <small>
                قرض الحسنه
            </small>

        </div>


        {{-- Menu --}}
        <nav class="customer-sidebar-menu">

            {{-- خانه --}}
            <a href="{{ url('/customer/dashboard') }}"
               class="customer-menu-item active">

                <span class="customer-menu-icon">
                    <i class="bi bi-house-fill"></i>
                </span>

                <span class="customer-menu-label">
                    خانه
                </span>

            </a>


            {{-- حساب‌ها --}}
            <a href="#"
               class="customer-menu-item">

                <span class="customer-menu-icon">
                    <i class="bi bi-credit-card"></i>
                </span>

                <span class="customer-menu-label">
                    حساب‌ها
                </span>

            </a>


            {{-- تراکنش‌ها --}}
            <a href="#"
               class="customer-menu-item">

                <span class="customer-menu-icon">
                    <i class="bi bi-arrow-left-right"></i>
                </span>

                <span class="customer-menu-label">
                    تراکنش‌ها
                </span>

            </a>


            {{-- وام‌ها --}}
            <a href="#"
               class="customer-menu-item">

                <span class="customer-menu-icon">
                    <i class="bi bi-cash-coin"></i>
                </span>

                <span class="customer-menu-label">
                    وام‌ها
                </span>

            </a>


            {{-- خدمات --}}
            <a href="#"
               class="customer-menu-item">

                <span class="customer-menu-icon">
                    <i class="bi bi-grid"></i>
                </span>

                <span class="customer-menu-label">
                    خدمات
                </span>

            </a>


            {{-- پیام‌ها --}}
            <a href="#"
               class="customer-menu-item">

                <span class="customer-menu-icon">
                    <i class="bi bi-chat-square-text"></i>
                </span>

                <span class="customer-menu-label">
                    پیام
                </span>

            </a>


            {{-- تنظیمات --}}
            <a href="#"
               class="customer-menu-item">

                <span class="customer-menu-icon">
                    <i class="bi bi-gear"></i>
                </span>

                <span class="customer-menu-label">
                    تنظیمات
                </span>

            </a>

        </nav>


        {{-- خروج --}}
        <div class="customer-sidebar-footer">

            <a href="#"
               class="customer-menu-item customer-menu-logout">

                <span class="customer-menu-icon">
                    <i class="bi bi-box-arrow-right"></i>
                </span>

                <span class="customer-menu-label">
                    خروج
                </span>

            </a>

        </div>

    </aside>


    {{-- =====================================================
         MAIN
         ===================================================== --}}
    <div class="customer-main">


        {{-- =================================================
             HEADER
             ================================================= --}}
        <header class="customer-header">


            {{-- ---------------------------------------------
                 User
                 --------------------------------------------- --}}
            <div class="customer-header-user">

                <div class="customer-header-avatar">

                    <div class="customer-header-avatar-inner">
                        <i class="bi bi-person-fill"></i>
                    </div>

                </div>


                <div class="customer-header-welcome">

                    <div class="customer-header-greeting">

                        @hasSection('header_title')

                            @yield('header_title')

                        @else

                            سلام
                            {{ auth()->user()?->customer?->full_name
                                ?? auth()->user()?->username
                                ?? 'محمدرضا برزگر' }}

                        @endif

                    </div>

                    <div class="customer-header-subtitle">

                        @hasSection('header_subtitle')

                            @yield('header_subtitle')

                        @else

                            خوش آمدید به پنل مشتری

                        @endif

                    </div>

                </div>

            </div>


            {{-- ---------------------------------------------
                 Header Left
                 --------------------------------------------- --}}
            <div class="customer-header-left">


                {{-- پیام --}}
                <button type="button"
                        class="customer-header-action"
                        aria-label="پیام‌ها">

                    <i class="bi bi-chat-dots"></i>

                    <span class="customer-header-badge">
                        0
                    </span>

                </button>


                {{-- اعلان --}}
                <button type="button"
                        class="customer-header-action"
                        aria-label="اعلان‌ها">

                    <i class="bi bi-bell"></i>

                    <span class="customer-header-badge">
                        0
                    </span>

                </button>


                {{-- تاریخ --}}
                <div class="customer-header-date">

                    <span class="customer-header-datetime-icon">
                        <i class="bi bi-calendar3"></i>
                    </span>

                    <span>
                        شنبه ۱۷ مرداد ۱۴۰۵
                    </span>

                </div>


                {{-- ساعت --}}
                <div class="customer-header-time">

                    <span class="customer-header-datetime-icon">
                        <i class="bi bi-clock"></i>
                    </span>

                    <span>
                        ۱۷:۳۵
                    </span>

                </div>

            </div>

        </header>


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

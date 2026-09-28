<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>صندوق قرض‌الحسنه شهید مطهری شیراز- داریون</title>

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])
</head>

<body class="home-page">

{{-- =====================================================
    HEADER
====================================================== --}}
<header class="home-header">

    <div class="home-container home-header-inner">

        <a href="{{ url('/') }}" class="home-logo">

            <div class="home-logo-icon">
                <i class="bi bi-bank2"></i>
            </div>

            <div class="home-logo-text">
                <strong>صندوق قرض‌الحسنه</strong>
                <span>شهید مطهری شیراز- داریون</span>
            </div>

        </a>


        <nav class="home-nav">

            <a href="#services">خدمات صندوق</a>
            <a href="#about">درباره صندوق</a>
            <a href="#news">اطلاعیه‌ها</a>
            <a href="#contact">ارتباط با ما</a>

        </nav>


        <div class="home-header-actions">

            @auth

                @if(auth()->user()->role === \App\Enums\UserRole::CUSTOMER)

                    <a
                        href="{{ route('customer.dashboard') }}"
                        class="home-btn home-btn-outline"
                    >
                        <i class="bi bi-person-circle me-1"></i>
                        داشبورد
                    </a>

                @else

                    <a
                        href="{{ route('dashboard') }}"
                        class="home-btn home-btn-outline"
                    >
                        <i class="bi bi-speedometer2 me-1"></i>
                        داشبورد
                    </a>

                @endif


                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="d-inline"
                >
                    @csrf

                    <button
                        type="submit"
                        class="home-btn home-btn-primary border-0"
                    >
                        <i class="bi bi-box-arrow-right me-1"></i>
                        خروج
                    </button>
                </form>

            @else

                <a
                    href="{{ route('login') }}"
                    class="home-btn home-btn-outline"
                >
                    ورود
                </a>

                <a
                    href="{{ route('customer-activation.create') }}"
                    class="home-btn home-btn-primary"
                >
                    عضویت
                </a>

            @endauth

        </div>

    </div>

</header>




{{-- =====================================================
    HERO
====================================================== --}}
<main>

    <section class="home-hero">

        <div class="home-container home-hero-inner">

            <div class="home-hero-content">

                <h3 class="home-hero-badge">
                    <i class="bi bi-heart-fill"></i>
                    صندوق قرض‌الحسنه
                </h3>

                <h2>
                    شهید مطهری شیراز- داریون
                    <br>
                    برای آینده‌ای بهتر
                </h2>

                <p>
                    با پس‌انداز، دریافت وام قرض‌الحسنه و
                    همراهی با یکدیگر، آینده‌ای مطمئن‌تر
                    برای اعضای صندوق می‌سازیم.
                </p>

                <div class="home-hero-actions">

                    <a
                        href="{{ route('login') }}"
                        class="home-btn home-btn-primary home-btn-large"
                    >
                        ورود به سامانه
                        <i class="bi bi-arrow-left"></i>
                    </a>

                    <a
                        href="#services"
                        class="home-btn home-btn-light home-btn-large"
                    >
                        آشنایی با خدمات
                    </a>

                </div>

            </div>


            <div class="home-hero-visual">

                <div class="home-hero-image-wrapper">

                    <div class="home-hero-image-placeholder">

                        <i class="bi bi-piggy-bank-fill"></i>

                        <div>
                            <strong>پس‌انداز و سرمایه‌گذاری</strong>
                            <span>
                                همراهی امروز، آرامش فردا
                            </span>
                        </div>

                    </div>

                </div>

                <div class="home-hero-arrow">
                    <i class="bi bi-chevron-left"></i>
                </div>

            </div>

        </div>

    </section>


    {{-- =====================================================
        SERVICES
    ====================================================== --}}
    <section
        id="services"
        class="home-section home-services"
    >

        <div class="home-container">

            <div class="home-section-header">

                <span>خدمات صندوق</span>

                <h2>
                    خدماتی برای اعضای صندوق
                </h2>

                <p>
                    امکانات اصلی صندوق در یک نگاه
                </p>

            </div>


            <div class="home-services-grid">

                {{-- ۱. واریز به حساب پس‌انداز --}}
                <article class="home-service-card">

                    <div class="home-service-header">

                        <div class="home-service-icon purple">
                            <i class="bi bi-wallet2"></i>
                        </div>

                        <h3>واریز به حساب پس‌انداز</h3>

                    </div>

                    <div class="home-service-content">

                        <p>
                            افزایش موجودی حساب پس‌انداز به‌صورت
                            آسان و سریع.
                        </p>

                    </div>

                    <a
                        href="{{ route('customer.savings.deposit.create') }}"
                        class="home-service-footer"
                    >
                        <span>واریز وجه</span>
                        <i class="bi bi-arrow-left"></i>
                    </a>

                </article>


                {{-- ۲. برداشت از حساب پس‌انداز --}}
                <article class="home-service-card">

                    <div class="home-service-header">

                        <div class="home-service-icon blue">
                            <i class="bi bi-wallet"></i>
                        </div>

                        <h3>برداشت از حساب پس‌انداز</h3>

                    </div>

                    <div class="home-service-content">

                        <p>
                            ثبت درخواست برداشت از موجودی حساب
                            پس‌انداز.
                        </p>

                    </div>

                    <a
                        href="{{ route('customer.savings.withdrawal.create') }}"
                        class="home-service-footer"
                    >
                        <span>درخواست برداشت</span>
                        <i class="bi bi-arrow-left"></i>
                    </a>

                </article>


                {{-- ۳. درخواست وام --}}
                <article class="home-service-card">

                    <div class="home-service-header">

                        <div class="home-service-icon green">
                            <i class="bi bi-cash-coin"></i>
                        </div>

                        <h3>درخواست وام</h3>

                    </div>

                    <div class="home-service-content">

                        <p>
                            ثبت درخواست وام قرض‌الحسنه و
                            پیگیری وضعیت آن.
                        </p>

                    </div>

                    <a
                        href="{{ route('customer.loan-request.create') }}"
                        class="home-service-footer"
                    >
                        <span>درخواست وام</span>
                        <i class="bi bi-arrow-left"></i>
                    </a>

                </article>


                {{-- ۴. پرداخت قسط خودم --}}
                <article class="home-service-card">

                    <div class="home-service-header">

                        <div class="home-service-icon purple">
                            <i class="bi bi-credit-card"></i>
                        </div>

                        <h3>پرداخت قسط خودم</h3>

                    </div>

                    <div class="home-service-content">

                        <p>
                            مشاهده اقساط و پرداخت قسط وام
                            خود از طریق سامانه.
                        </p>

                    </div>

                    <a
                        href="{{ route('customer.installments.index') }}"
                        class="home-service-footer"
                    >
                        <span>پرداخت قسط</span>
                        <i class="bi bi-arrow-left"></i>
                    </a>

                </article>


                {{-- ۵. واریز کمک --}}
                <article class="home-service-card">

                    <div class="home-service-header">

                        <div class="home-service-icon pink">
                            <i class="bi bi-heart"></i>
                        </div>

                        <h3>واریز کمک</h3>

                    </div>

                    <div class="home-service-content">

                        <p>
                            مشارکت در امور خیر و کمک به
                            نیازمندان.
                        </p>

                    </div>

                    <a
                        href="{{ route('donation.create') }}"
                        class="home-service-footer"
                    >
                        <span>واریز کمک</span>
                        <i class="bi bi-arrow-left"></i>
                    </a>

                </article>


                {{-- ۶. پرداخت قسط دیگران --}}
                <article class="home-service-card">

                    <div class="home-service-header">

                        <div class="home-service-icon orange">
                            <i class="bi bi-credit-card-2-front"></i>
                        </div>

                        <h3>پرداخت قسط دیگران</h3>

                    </div>

                    <div class="home-service-content">

                        <p>
                            پرداخت اقساط وام اعضای دیگر
                            از طریق سامانه.
                        </p>

                    </div>

                    <a
                        href="{{ route('customer.installments.others.create') }}"
                        class="home-service-footer"
                    >
                        <span>پرداخت قسط</span>
                        <i class="bi bi-arrow-left"></i>
                    </a>

                </article>


                {{-- ۷. واریز به حساب پس‌انداز دیگران --}}
                <article class="home-service-card">

                    <div class="home-service-header">

                        <div class="home-service-icon teal">
                            <i class="bi bi-people"></i>
                        </div>

                        <h3>واریز به حساب پس‌انداز دیگران</h3>

                    </div>

                    <div class="home-service-content">

                        <p>
                            واریز وجه به حساب پس‌انداز
                            سایر اعضای صندوق.
                        </p>

                    </div>

                    <a
                        href="{{ route('customer.savings-transfer.create') }}"
                        class="home-service-footer"
                    >
                        <span>واریز وجه</span>
                        <i class="bi bi-arrow-left"></i>
                    </a>

                </article>

            </div>

        </div>

    </section>




    {{-- =====================================================
        STATS
    ====================================================== --}}
    <section class="home-stats">

        <div class="home-container">

            <div class="home-section-header">

                <span>آمار صندوق</span>

                <h2>صندوق در یک نگاه</h2>

                <h6>
                    فعالیت صندوق تا تاریخ
                    @if(!empty($stats['statistics_date']))
                        {{ fa_number(\Morilog\Jalali\Jalalian::fromCarbon($stats['statistics_date'])->format('Y/m/d')) }}
                    @else
                        ---
                    @endif
                </h6>

            </div>

            <div class="home-stats-grid">

                {{-- اعضا --}}
                <div class="home-stat-card">

                    <div class="home-stat-icon purple">
                        <i class="bi bi-people"></i>
                    </div>

                    <strong>
                        {{ fa_number($stats['members'] ?? 0) }}
                    </strong>

                    <span>عضو صندوق</span>

                </div>


                {{-- وام‌های پرداخت‌شده --}}
                <div class="home-stat-card">

                    <div class="home-stat-icon blue">
                        <i class="bi bi-bank"></i>
                    </div>

                    <strong>
                        {{ fa_number($stats['paid_loans'] ?? 0) }}
                    </strong>

                    <span>وام پرداخت‌شده</span>

                </div>


                {{-- مبلغ وام‌ها --}}
                <div class="home-stat-card">

                    <div class="home-stat-icon green">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                    <strong>
                        {{ fa_money($stats['paid_loan_amount'] ?? 0) }}
                    </strong>

                    <span>مبلغ وام‌های پرداختی</span>

                    <small>ریال</small>

                </div>


                {{-- کمک‌ها --}}
                <div class="home-stat-card">

                    <div class="home-stat-icon pink">
                        <i class="bi bi-heart"></i>
                    </div>

                    <strong>
                        {{ fa_number($stats['donations'] ?? 0) }}
                    </strong>

                    <span>کمک‌های ثبت‌شده</span>

                </div>

            </div>

        </div>

    </section>


    {{-- =====================================================
        ABOUT
    ====================================================== --}}

    <section
        id="about"
        class="home-section home-about"
    >

        <div class="home-container home-about-inner">

            {{-- تصویر / معرفی صندوق --}}
            <div class="home-about-image">

                <div class="home-about-placeholder">

                    <div class="home-about-icon">
                        <i class="bi bi-building"></i>
                    </div>

                    <strong>
                        صندوق قرض‌الحسنه
                    </strong>

                    <span>
                        شهید مطهری شیراز- داریون
                    </span>

                    <div class="home-about-mini-stats">

                        <div>
                            <i class="bi bi-shield-check"></i>
                            <span>اعتماد</span>
                        </div>

                        <div>
                            <i class="bi bi-people"></i>
                            <span>اعضا</span>
                        </div>

                        <div>
                            <i class="bi bi-hand-thumbs-up"></i>
                            <span>همراهی</span>
                        </div>

                    </div>

                </div>

            </div>


            {{-- محتوای درباره صندوق --}}
            <div class="home-about-content">

                <span class="home-section-label">
                    درباره صندوق
                </span>

                <h2>
                    همراهی اعضا،
                    <br>
                    سرمایه واقعی صندوق است
                </h2>

                <p>
                    صندوق قرض‌الحسنه شهید مطهری شیراز- داریون با هدف
                    ایجاد بستری مطمئن برای پس‌انداز، دریافت
                    تسهیلات قرض‌الحسنه و حمایت از اعضای خود
                    فعالیت می‌کند.
                </p>

                <div class="home-about-features">

                    <div>
                        <i class="bi bi-shield-check"></i>
                        <span>امنیت و اعتماد</span>
                    </div>

                    <div>
                        <i class="bi bi-phone"></i>
                        <span>خدمات آنلاین</span>
                    </div>

                    <div>
                        <i class="bi bi-people"></i>
                        <span>همراهی اعضا</span>
                    </div>

                </div>

            </div>

        </div>

    </section>




    {{-- =====================================================
        NEWS
    ====================================================== --}}
    <section
        id="news"
        class="home-section home-news"
    >

        <div class="home-container">

            <div class="home-section-header">

                <span>آخرین مطالب</span>

                <h2>
                    اطلاعیه‌ها و اخبار صندوق
                </h2>

                <p>
                    آخرین اطلاعیه‌ها، اخبار و راهنمای خدمات صندوق
                </p>

            </div>


            <div class="home-news-grid">


                {{-- اطلاعیه --}}
                <article class="home-news-card">

                    <div class="home-news-icon purple">
                        <i class="bi bi-megaphone"></i>
                    </div>

                    <div class="home-news-content">

                        <div class="home-news-meta">
                            <span>اطلاعیه</span>
                            <i class="bi bi-dot"></i>
                            <small>صندوق</small>
                        </div>

                        <h3>
                            اطلاعیه صندوق
                        </h3>

                        <p>
                            آخرین اطلاعیه‌های صندوق در این بخش
                            به اطلاع اعضای محترم خواهد رسید.
                        </p>

                        <span class="home-news-link text-muted">
                            به‌زودی
                            <i class="bi bi-clock"></i>
                        </span>

                    </div>

                </article>


                {{-- اخبار --}}
                <article class="home-news-card">

                    <div class="home-news-icon blue">
                        <i class="bi bi-calendar-event"></i>
                    </div>

                    <div class="home-news-content">

                        <div class="home-news-meta">
                            <span>خبر</span>
                            <i class="bi bi-dot"></i>
                            <small>رویدادها</small>
                        </div>

                        <h3>
                            اخبار و رویدادهای صندوق
                        </h3>

                        <p>
                            اخبار، رویدادها و فعالیت‌های مهم صندوق
                            در این قسمت نمایش داده می‌شود.
                        </p>

                        <span class="home-news-link text-muted">
                            به‌زودی
                            <i class="bi bi-clock"></i>
                        </span>

                    </div>

                </article>


                {{-- راهنما --}}
                <article class="home-news-card">

                    <div class="home-news-icon green">
                        <i class="bi bi-info-circle"></i>
                    </div>

                    <div class="home-news-content">

                        <div class="home-news-meta">
                            <span>راهنما</span>
                            <i class="bi bi-dot"></i>
                            <small>خدمات آنلاین</small>
                        </div>

                        <h3>
                            راهنمای استفاده از سامانه
                        </h3>

                        <p>
                            راهنمای استفاده از خدمات آنلاین صندوق
                            برای اعضا در این بخش قرار می‌گیرد.
                        </p>

                        <span class="home-news-link text-muted">
                            به‌زودی
                            <i class="bi bi-clock"></i>
                        </span>

                    </div>

                </article>

            </div>

        </div>

    </section>

</main>


{{-- =====================================================
    FOOTER
====================================================== --}}
<footer
    id="contact"
    class="home-footer"
>
    <div class="home-container">

        <div class="home-footer-grid">

            {{-- معرفی صندوق --}}
            <div class="home-footer-brand">

                <div class="home-logo home-logo-footer">

                    <div class="home-logo-icon">
                        <i class="bi bi-bank2"></i>
                    </div>

                    <div class="home-logo-text">
                        <strong>صندوق قرض‌الحسنه</strong>
                        <span>شهید مطهری شیراز- داریون</span>
                    </div>

                </div>

                <p>
                    همراه شما برای ساختن آینده‌ای بهتر،
                    با هدف حمایت از اعضا و گسترش فرهنگ قرض‌الحسنه.
                </p>

            </div>


            {{-- دسترسی سریع --}}
            <div class="home-footer-column">

                <h3>دسترسی سریع</h3>

                <a href="#services">
                    خدمات صندوق
                </a>

                <a href="#about">
                    درباره صندوق
                </a>

                <a href="#news">
                    اطلاعیه‌ها و اخبار
                </a>

                <a href="{{ route('login') }}">
                    ورود به سامانه
                </a>

            </div>


            {{-- خدمات --}}
            <div class="home-footer-column">

                <h3>خدمات صندوق</h3>

                <a href="{{ route('customer.savings.deposit.create') }}">
                    پس‌انداز
                </a>

                <a href="{{ route('customer.loan-request.create') }}">
                    وام قرض‌الحسنه
                </a>

                <a href="{{ route('customer.installments.index') }}">
                    پرداخت اقساط
                </a>

                <a href="{{ route('donation.create') }}">
                    کمک به نیازمندان
                </a>

            </div>


            {{-- ارتباط با ما --}}
            <div class="home-footer-column home-footer-contact">

                <h3>ارتباط با ما</h3>

                <span>
                    <i class="bi bi-geo-alt"></i>
                    <span>آدرس صندوق : شیراز - داریون بلوار کشاورز روبروی بانک ملی</span>
                </span>

                <a href="tel:07132613217">
                    <i class="bi bi-telephone"></i>
                    <span>تلفن : </span><span>07132613217</span>
                </a>

                <span>
                    <i class="bi bi-envelope"></i>
                    <span>ایمیل صندوق</span>
                </span>

            </div>

        </div>


        {{-- پایین Footer --}}
        <div class="home-footer-bottom">

            <span>
                © تمامی حقوق محفوظ است.
            </span>

        </div>

    </div>
</footer>

</body>
</html>


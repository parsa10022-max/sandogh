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
                        ?? 'مشتری محترم' }}

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




        {{-- اعلان --}}
        @php
            $unreadNotificationsCount = auth()->user()
                ->notifications()
                ->whereNull('read_at')
                ->count();
        @endphp

        <a href="{{ route('customer.notifications.index') }}"
           class="customer-header-action"
           aria-label="اعلان‌ها">

            <i class="bi bi-bell"></i>

            @if($unreadNotificationsCount > 0)
                <span class="customer-header-badge">
            {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
        </span>
            @endif

        </a>

        @php
            $jalaliDateService = app(\App\Services\Date\JalaliDateService::class);
        @endphp
        {{-- تاریخ --}}
        <div class="customer-header-date">

    <span class="customer-header-datetime-icon">
        <i class="bi bi-calendar3"></i>
    </span>

            <span>
        {{ $jalaliDateService->todayFull() }}
    </span>

        </div>


        {{-- ساعت --}}
        <div class="customer-header-time">

    <span class="customer-header-datetime-icon">
        <i class="bi bi-clock"></i>
    </span>

            <span>
        {{ $jalaliDateService->currentTime() }}
    </span>

        </div>


        <form method="POST"
              action="{{ route('logout') }}"
              class="customer-header-logout-form">

            @csrf

            <button type="submit"
                    class="customer-header-logout"
                    title="خروج"
                    aria-label="خروج">

                <i class="bi bi-box-arrow-right"></i>

                <span>
            خروج
        </span>

            </button>

        </form>
    </div>

</header>

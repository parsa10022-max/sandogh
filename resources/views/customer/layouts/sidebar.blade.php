blade
<aside class="customer-sidebar">

    {{-- =====================================================
         BRAND
    ====================================================== --}}
    <div class="customer-sidebar-brand">
        <div class="customer-brand-logo">
            <i class="bi bi-bank2"></i>
        </div>
        <a href="{{ route('customer.dashboard') }}"
           class="customer-sidebar-brand-link"
           aria-label="صندوق قرض الحسنه شهید مطهری شیراز - داریون">



            <div class="customer-brand-text">

                <strong>
                    صندوق قرض الحسنه شهید مطهری
                </strong>

                <small>
                    شیراز - داریون
                </small>

            </div>

        </a>

    </div>


    {{-- =====================================================
         MAIN MENU
    ====================================================== --}}
    <nav class="customer-sidebar-menu"
         aria-label="منوی اصلی">


        {{-- =================================================
             خانه
        ================================================== --}}
        <a href="{{ route('customer.dashboard') }}"
           class="customer-menu-item
           {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">

            <span class="customer-menu-icon">
                <i class="bi bi-house-fill"></i>
            </span>

            <span class="customer-menu-label">
                خانه
            </span>

        </a>

        {{-- =================================================
             تراکنش‌ها
        ================================================== --}}
        <a href="{{ route('customer.savings.transactions') }}"
           class="customer-menu-item
           {{ request()->routeIs('customer.savings.transactions') ? 'active' : '' }}">

            <span class="customer-menu-icon">
                <i class="bi bi-arrow-left-right"></i>
            </span>

            <span class="customer-menu-label">
                تراکنش‌ها
            </span>

        </a>


        {{-- =================================================
             وام‌ها
        ================================================== --}}
        <a href="{{ route('customer.loans.index') }}"
           class="customer-menu-item
           {{ request()->routeIs('customer.loans.*') ? 'active' : '' }}">

            <span class="customer-menu-icon">
                <i class="bi bi-cash-coin"></i>
            </span>

            <span class="customer-menu-label">
                وام‌ها
            </span>

        </a>


        {{-- =================================================
             خدمات
        ================================================== --}}
        <a href="#"
           class="customer-menu-item
           {{ request()->routeIs('customer.services.*') ? 'active' : '' }}">

            <span class="customer-menu-icon">
                <i class="bi bi-grid"></i>
            </span>

            <span class="customer-menu-label">
                خدمات
            </span>

        </a>


        {{-- =================================================
             پیام‌ها
        ================================================== --}}
        <a href="#"
           class="customer-menu-item
           {{ request()->routeIs('customer.messages.*') ? 'active' : '' }}">

            <span class="customer-menu-icon">
                <i class="bi bi-chat-square-text"></i>
            </span>

            <span class="customer-menu-label">
                پیام‌ها
            </span>

            {{-- تعداد پیام‌ها --}}
            <span class="customer-menu-badge">
                0
            </span>

        </a>


        {{-- =================================================
             اعلان‌ها
        ================================================== --}}
        <a href="{{ route('customer.notifications.index') }}"
           class="customer-menu-item
           {{ request()->routeIs('customer.notifications.*') ? 'active' : '' }}">

            <span class="customer-menu-icon">
                <i class="bi bi-bell"></i>
            </span>

            <span class="customer-menu-label">
                اعلان‌ها
            </span>

            @php
                $sidebarUnreadNotificationsCount = auth()->user()
                    ?->notifications()
                    ->whereNull('read_at')
                    ->count() ?? 0;
            @endphp

            @if($sidebarUnreadNotificationsCount > 0)

                <span class="customer-menu-badge">

                    {{ $sidebarUnreadNotificationsCount > 99
                        ? '99+'
                        : $sidebarUnreadNotificationsCount }}

                </span>

            @endif

        </a>


        {{-- =================================================
             تنظیمات
        ================================================== --}}
        <a href="#"
           class="customer-menu-item
           {{ request()->routeIs('customer.settings.*') ? 'active' : '' }}">

            <span class="customer-menu-icon">
                <i class="bi bi-gear"></i>
            </span>

            <span class="customer-menu-label">
                تنظیمات
            </span>

        </a>

    </nav>


    {{-- =====================================================
         SIDEBAR FOOTER
    ====================================================== --}}
    <div class="customer-sidebar-footer">


        {{-- =================================================
             DIVIDER
        ================================================== --}}
        <div class="customer-sidebar-divider"></div>


        {{-- =================================================
             پروفایل
        ================================================== --}}
        <a href="#"
           class="customer-menu-item
           {{ request()->routeIs('customer.profile.*') ? 'active' : '' }}">

            <span class="customer-menu-icon">
                <i class="bi bi-person-circle"></i>
            </span>

            <span class="customer-menu-label">
                پروفایل
            </span>

        </a>


        {{-- =================================================
             خروج
        ================================================== --}}
        <form method="POST"
              action="{{ route('logout') }}"
              class="customer-sidebar-logout-form">

            @csrf

            <button type="submit"
                    class="customer-menu-item customer-menu-logout">

                <span class="customer-menu-icon">
                    <i class="bi bi-box-arrow-right"></i>
                </span>

                <span class="customer-menu-label">
                    خروج
                </span>

            </button>

        </form>

    </div>

</aside>

@php
    $jalaliDateService = app(\App\Services\Date\JalaliDateService::class);
@endphp
<header class="admin-header">

    <div class="admin-header-inner">

        {{-- Sidebar Toggle --}}
        <button
            type="button"
            class="admin-header-toggle"
            id="adminSidebarToggle"
            aria-label="باز و بسته کردن منو"
        >
            <i class="bi bi-list"></i>
        </button>


        {{-- Page Title --}}
        <div class="admin-header-title">

            <h1>
                @yield('title', 'داشبورد مدیریت')
            </h1>

        </div>

        {{-- Date & Time --}}
        <div class="admin-header-datetime">

            <div class="admin-header-time">
                <i class="bi bi-clock"></i>
                <span>
            {{ $jalaliDateService->currentTime() }}
        </span>
            </div>

            <div class="admin-header-date">
                <i class="bi bi-calendar3"></i>
                <span>
            {{ $jalaliDateService->todayFull() }}
        </span>
            </div>

        </div>
        {{-- Header Actions --}}
        <div class="admin-header-actions">

            @auth

                {{-- Notifications --}}
                <a
                    href="#"
                    class="admin-header-action notification-action"
                    aria-label="اعلان‌ها"
                >
                    <i class="bi bi-bell"></i>

                    @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                        <span class="notification-badge">
                            {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>


                {{-- User --}}
                <div class="dropdown">

                    <button
                        type="button"
                        class="admin-user-menu dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >

                        <span class="admin-user-avatar">
                            <i class="bi bi-person"></i>
                        </span>

                        <span class="admin-user-info">

                            <strong>
                                {{ auth()->user()->customer?->full_name ?? auth()->user()->username }}
                            </strong>

                            <small>
                                {{ auth()->user()->role->label() }}
                            </small>

                        </span>

                    </button>


                    <ul class="dropdown-menu dropdown-menu-end admin-user-dropdown">

                        <li class="admin-user-dropdown-header">

                            <strong>
                                {{ auth()->user()->customer?->full_name ?? auth()->user()->username }}
                            </strong>

                            <small>
                                {{ auth()->user()->role->label() }}
                            </small>

                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a
                                href="#"
                                class="dropdown-item"
                            >
                                <i class="bi bi-person"></i>
                                <span>پروفایل</span>
                            </a>
                        </li>

                        <li>
                            <a
                                href="#"
                                class="dropdown-item"
                            >
                                <i class="bi bi-key"></i>
                                <span>تغییر رمز عبور</span>
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>

                            <form
                                method="POST"
                                action="{{ route('logout') }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="dropdown-item text-danger"
                                >
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>خروج</span>
                                </button>

                            </form>

                        </li>

                    </ul>

                </div>

            @endauth

        </div>

    </div>

</header>


<aside class="admin-sidebar" id="adminSidebar">

    @php
        $user = auth()->user();
    @endphp


    {{-- =====================================================
         SIDEBAR HEADER
         ===================================================== --}}

    <div class="admin-sidebar-header">

        <div class="admin-sidebar-brand">

            <div class="admin-sidebar-brand-icon">
                <i class="bi bi-wallet2"></i>
            </div>

            <div class="admin-sidebar-brand-text">

                <div class="admin-sidebar-brand-title">
                    صندوق
                </div>

                <div class="admin-sidebar-brand-subtitle">
                    مدیریت صندوق
                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         SIDEBAR MENU
         ===================================================== --}}

    <nav class="admin-sidebar-menu">

        @foreach(config('menu', []) as $index => $group)

            @php
                $children = collect($group['children'] ?? []);

                $visibleChildren = $children->filter(function ($item) use ($user) {

                    if (!isset($item['roles'])) {
                        return true;
                    }

                    if (!$user) {
                        return false;
                    }

                    return in_array(
                        $user->role,
                        $item['roles'],
                        true
                    );
                });

                if ($visibleChildren->isEmpty()) {
                    continue;
                }

                $groupActive = $visibleChildren->contains(function ($item) {

                    $activeRoutes = $item['active'] ?? [];

                    if (empty($activeRoutes) && isset($item['route'])) {
                        $activeRoutes = [$item['route']];
                    }

                    return collect($activeRoutes)->contains(
                        fn ($route) => request()->routeIs($route)
                    );
                });
            @endphp

            <div class="admin-sidebar-group">

                <button
                    type="button"
                    class="admin-sidebar-group-toggle
                {{ $groupActive ? 'is-active' : 'collapsed' }}"
                    data-bs-toggle="collapse"
                    data-bs-target="#adminMenu{{ $index }}"
                    aria-controls="adminMenu{{ $index }}"
                    aria-expanded="{{ $groupActive ? 'true' : 'false' }}"
                >

            <span class="admin-sidebar-group-label">

                <span class="admin-sidebar-group-icon">
                    <i class="bi bi-{{ $group['icon'] ?? 'circle' }}"></i>
                </span>

                <span class="admin-sidebar-group-title">
                    {{ $group['title'] ?? '' }}
                </span>

            </span>

                    <i class="bi bi-chevron-down admin-sidebar-chevron"></i>

                </button>


                <div
                    id="adminMenu{{ $index }}"
                    class="collapse {{ $groupActive ? 'show' : '' }}"
                >

                    <div class="admin-sidebar-submenu">

                        @foreach($visibleChildren as $item)

                            @php

                                $activeRoutes = $item['active'] ?? [];

                                if (empty($activeRoutes) && isset($item['route'])) {
                                    $activeRoutes = [$item['route']];
                                }

                                $active = collect($activeRoutes)->contains(
                                    fn ($route) => request()->routeIs($route)
                                );

                                $routeName = $item['route'] ?? null;

                                $hasRoute = $routeName
                                    && Route::has($routeName);

                            @endphp


                            <a
                                href="{{ $hasRoute ? route($routeName) : '#' }}"
                                class="admin-sidebar-link
                            {{ $active ? 'active' : '' }}
                                {{ !$hasRoute ? 'disabled' : '' }}"
                                @if(!$hasRoute)
                                aria-disabled="true"
                                @endif
                            >

                        <span class="admin-sidebar-link-icon">
                            <i class="bi bi-{{ $item['icon'] ?? 'circle' }}"></i>
                        </span>

                                <span class="admin-sidebar-link-title">
                            {{ $item['title'] ?? '' }}
                        </span>

                            </a>

                        @endforeach

                    </div>

                </div>

            </div>

        @endforeach

    </nav>

</aside>

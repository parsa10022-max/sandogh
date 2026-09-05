<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    @include('layouts.head')
</head>

<body class="admin-body">

<div class="admin-layout">

    {{-- Sidebar --}}
    @include('layouts.sidebar')

    <div class="admin-main">

        {{-- Header --}}
        @include('layouts.header')

        <main class="admin-content">

            @include('layouts.alerts')

            @hasSection('breadcrumb')
                <div class="admin-breadcrumb">
                    @yield('breadcrumb')
                </div>
            @endif

            @yield('content')

        </main>

        {{-- Footer --}}
        @include('layouts.footer')

    </div>

</div>

<div
    class="admin-sidebar-overlay"
    id="adminSidebarOverlay">
</div>

@stack('scripts')

</body>
</html>

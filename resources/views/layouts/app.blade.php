<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    @include('layouts.head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-light">

<div class="d-flex flex-column min-vh-100">

    {{-- Header --}}
    @include('layouts.header')

    <div class="container-fluid flex-grow-1">

        <div class="row">

            {{-- Sidebar --}}
            <aside class="col-md-3 col-lg-2 p-0 bg-white border-start shadow-sm">

                @include('layouts.sidebar')

            </aside>

            {{-- Main Content --}}
            <main class="col-md-9 col-lg-10 py-4 px-4">

                @include('layouts.alerts')

                @hasSection('breadcrumb')

                    <div class="mb-3">
                        @yield('breadcrumb')
                    </div>

                @endif

                @yield('content')

            </main>

        </div>

    </div>

    {{-- Footer --}}
    @include('layouts.footer')

</div>

@stack('scripts')

</body>
</html>

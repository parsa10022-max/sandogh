<header class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">

    <div class="container-fluid">

        {{-- Logo --}}
        <a
            class="navbar-brand fw-bold d-flex align-items-center"
            href="{{ route('dashboard') }}"
        >

            <i class="bi bi-bank2 fs-4 ms-2"></i>

            {{ config('app.name') }}

        </a>

        {{-- Mobile Toggle --}}
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarContent"
        >

            <span class="navbar-toggler-icon"></span>

        </button>

        <div
            class="collapse navbar-collapse"
            id="navbarContent"
        >

            <div class="ms-auto d-flex align-items-center gap-3">

                @auth

                    {{-- User Info --}}
                    <div class="text-end text-white">

                        <div class="fw-bold">

                            {{ auth()->user()->customer?->full_name ?? auth()->user()->username }}

                        </div>

                        <small class="opacity-75">

                            {{ auth()->user()->role->label() }}

                        </small>

                    </div>

                    {{-- User Menu --}}
                    <div class="dropdown">

                        <button
                            class="btn btn-outline-light dropdown-toggle"
                            data-bs-toggle="dropdown"
                            type="button"
                        >

                            <i class="bi bi-person-circle"></i>

                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow">

                            <li>

                                <a
                                    href="#"
                                    class="dropdown-item"
                                >

                                    <i class="bi bi-person ms-2"></i>

                                    پروفایل

                                </a>

                            </li>

                            <li>

                                <a
                                    href="#"
                                    class="dropdown-item"
                                >

                                    <i class="bi bi-key ms-2"></i>

                                    تغییر رمز عبور

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

                                        <i class="bi bi-box-arrow-right ms-2"></i>

                                        خروج

                                    </button>

                                </form>

                            </li>

                        </ul>

                    </div>

                @endauth

            </div>

        </div>

    </div>

</header>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>ورود به سامانه</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="{{ asset('auth.css') }}">

    <style>
        body {
            font-family: 'Vazirmatn', Tahoma, sans-serif !important;
        }

        .login-icon {
            width: 64px;
            height: 64px;
            background: #f0ebfa;
            color: #6f42c1;
        }

        .login-card {
            border-radius: 20px;
        }

        .login-link {
            color: #6f42c1;
        }

        .login-link:hover {
            color: #59359c;
        }
    </style>
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-12 col-md-6 col-lg-5">

            <div class="card border-0 shadow-sm login-card">

                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">

                        <div
                            class="login-icon d-inline-flex align-items-center justify-content-center rounded-4 mb-3"
                        >
                            <i class="bi bi-person-lock fs-3"></i>
                        </div>

                        <h1 class="h5 fw-bold mb-2">
                            ورود به سامانه
                        </h1>

                        <p class="text-muted small mb-0">
                            برای استفاده از امکانات صندوق وارد حساب کاربری خود شوید.
                        </p>

                    </div>

                    @if(session('success'))
                        <div class="alert alert-success small">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger small">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger small">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('login.store') }}"
                    >
                        @csrf

                        <div class="mb-3">

                            <label
                                for="username"
                                class="form-label fw-semibold"
                            >
                                نام کاربری
                            </label>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="{{ old('username') }}"
                                class="form-control form-control-lg"
                                autocomplete="username"
                                dir="ltr"
                                required
                            >

                        </div>

                        <div class="mb-4">

                            <label
                                for="password"
                                class="form-label fw-semibold"
                            >
                                رمز عبور
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control form-control-lg"
                                autocomplete="current-password"
                                dir="ltr"
                                required
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100 py-2"
                        >
                            <i class="bi bi-box-arrow-in-left me-1"></i>
                            ورود
                        </button>

                    </form>

                    <div class="text-center mt-4">

                        <a
                            href="{{ route('password.request') }}"
                            class="text-decoration-none small login-link"
                        >
                            فراموشی رمز عبور
                        </a>

                    </div>

                    <hr class="my-4">

                    <div class="text-center">

                        <p class="text-muted small mb-2">
                            عضو صندوق هستید ولی هنوز حساب کاربری ندارید؟
                        </p>

                        <a
                            href="{{ route('customer-activation.create') }}"
                            class="text-decoration-none fw-semibold login-link"
                        >
                            فعال‌سازی حساب کاربری
                            <i class="bi bi-person-plus me-1"></i>
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>

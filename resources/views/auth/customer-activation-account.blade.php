<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>ساخت حساب کاربری</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-12 col-md-6 col-lg-5">

            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">

                        <div
                            class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3"
                            style="
                                width: 64px;
                                height: 64px;
                                background: #f0ebfa;
                                color: #6f42c1;
                            "
                        >
                            <i class="bi bi-person-plus fs-3"></i>
                        </div>

                        <h1 class="h5 fw-bold mb-2">
                            ساخت حساب کاربری
                        </h1>

                        <p class="text-muted small mb-0">
                            اطلاعات ورود به سامانه را برای خود تعیین کنید.
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
                        action="{{ route('customer-activation.create-account') }}"
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

                            <div class="form-text">
                                نام کاربری باید یکتا باشد.
                            </div>

                        </div>

                        <div class="mb-3">

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
                                autocomplete="new-password"
                                dir="ltr"
                                required
                            >

                        </div>

                        <div class="mb-4">

                            <label
                                for="password_confirmation"
                                class="form-label fw-semibold"
                            >
                                تکرار رمز عبور
                            </label>

                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control form-control-lg"
                                autocomplete="new-password"
                                dir="ltr"
                                required
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100 py-2"
                        >
                            ایجاد حساب کاربری
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>

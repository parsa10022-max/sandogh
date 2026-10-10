<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>تأیید شماره موبایل</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="{{ asset('auth.css') }}">

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
                            <i class="bi bi-shield-check fs-3"></i>
                        </div>

                        <h1 class="h5 fw-bold mb-2">
                            تأیید شماره موبایل
                        </h1>

                        <p class="text-muted small mb-1">
                            کد تأیید برای شماره زیر ایجاد شده است:
                        </p>

                        <strong
                            dir="ltr"
                            class="d-block"
                        >
                            {{ $mobile }}
                        </strong>

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

                    @if($testOtp)
                        <div class="alert alert-warning text-center small">

                            <div class="fw-bold mb-1">
                                کد تست OTP
                            </div>

                            <div
                                dir="ltr"
                                style="
                                    font-size: 24px;
                                    letter-spacing: 5px;
                                    font-weight: 800;
                                "
                            >
                                {{ $testOtp }}
                            </div>

                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('customer-activation.verify-otp') }}"
                    >
                        @csrf

                        <div class="mb-4">

                            <label
                                for="code"
                                class="form-label fw-semibold"
                            >
                                کد تأیید ۶ رقمی
                            </label>

                            <input
                                type="text"
                                id="code"
                                name="code"
                                value="{{ old('code') }}"
                                class="form-control form-control-lg text-center"
                                inputmode="numeric"
                                maxlength="6"
                                autocomplete="one-time-code"
                                dir="ltr"
                                placeholder="123456"
                                required
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100 py-2"
                        >
                            تأیید کد
                        </button>

                    </form>

                    <div class="text-center mt-4">

                        <a
                            href="{{ route('customer-activation.create') }}"
                            class="text-decoration-none small"
                        >
                            تغییر شماره موبایل
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>

@extends('customer.layouts.app')

@section('title', 'تنظیمات')

@section('content')

    <div class="container-fluid px-0">

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-gear me-1"></i>
                تنظیمات
            </h1>

            <p class="text-muted mb-0">
                مدیریت تنظیمات حساب کاربری
            </p>
        </div>

        {{-- Account --}}
        <div class="card border rounded-4 shadow-sm mb-3">
            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 p-3 bg-light">
                        <i class="bi bi-person-circle fs-4"></i>
                    </div>

                    <div>
                        <h2 class="h6 fw-bold mb-1">
                            حساب کاربری
                        </h2>

                        <small class="text-muted">
                            اطلاعات ورود به حساب
                        </small>
                    </div>
                </div>

                <div class="row g-3">

                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted">
                            نام کاربری
                        </label>

                        <div class="form-control bg-light">
                            {{ $user->username }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted">
                            شماره موبایل
                        </label>

                        <div class="form-control bg-light">
                            {{ $user->mobile }}
                        </div>
                    </div>

                </div>

            </div>
        </div>

        {{-- Security --}}
        <div class="card border rounded-4 shadow-sm mb-3">
            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3">

                    <div class="rounded-3 p-3 bg-light">
                        <i class="bi bi-shield-lock fs-4"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="h6 fw-bold mb-1">
                            امنیت حساب
                        </h2>

                        <p class="text-muted small mb-0">
                            برای تغییر رمز عبور می‌توانید از این بخش استفاده کنید.
                        </p>
                    </div>

                    <button type="button"
                            class="btn btn-outline-primary"
                            disabled>
                        تغییر رمز
                    </button>

                </div>

            </div>
        </div>

        {{-- Notifications --}}
        <div class="card border rounded-4 shadow-sm">
            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3">

                    <div class="rounded-3 p-3 bg-light">
                        <i class="bi bi-bell fs-4"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h2 class="h6 fw-bold mb-1">
                            اعلان‌ها
                        </h2>

                        <p class="text-muted small mb-0">
                            اعلان‌های جدید در پنل مشتری نمایش داده می‌شوند.
                        </p>
                    </div>

                </div>

            </div>
        </div>

    </div>

@endsection


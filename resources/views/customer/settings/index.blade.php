@extends('customer.layouts.app')

@section('title', 'تنظیمات')

@section('content')

    <div class="container-fluid px-0 customer-settings">

        {{-- Page Header --}}
        <div class="customer-settings-header">
            <h1 class="customer-settings-title">
                <i class="bi bi-gear"></i>
                تنظیمات
            </h1>

            <p class="customer-settings-description">
                مدیریت حساب کاربری و امنیت
            </p>
        </div>

        {{-- Account --}}
        <div class="card customer-settings-card">
            <div class="card-body">

                <div class="customer-settings-card-header">
                    <div class="customer-settings-icon">
                        <i class="bi bi-person-circle"></i>
                    </div>

                    <div>
                        <h2 class="customer-settings-card-title">
                            حساب کاربری
                        </h2>
                        <span class="customer-settings-card-subtitle">
                            ویرایش اطلاعات ورود
                        </span>
                    </div>
                </div>

                {{-- Account Success --}}
                @if(session('account_success'))
                    <div class="alert alert-success customer-settings-alert">
                        <i class="bi bi-check-circle"></i>
                        {{ session('account_success') }}
                    </div>
                @endif

                {{-- Account Errors --}}
                @if($errors->account->any())
                    <div class="alert alert-danger customer-settings-alert customer-settings-errors">
                        <ul class="mb-0">
                            @foreach($errors->account->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Account Form --}}
                <form method="POST"
                      action="{{ route('customer.settings.account.update') }}"
                      novalidate
                      onsubmit="return validateAccountForm(this)">

                    @csrf
                    @method('PUT')

                    <div class="row g-2">

                        {{-- Username --}}
                        <div class="col-12 col-md-6">
                            <div class="customer-settings-form-group">
                                <label for="username" class="customer-settings-form-label">
                                    نام کاربری
                                </label>

                                <input type="text"
                                       id="username"
                                       name="username"
                                       value="{{ old('username', $user->username) }}"
                                       class="customer-settings-form-control"
                                       autocomplete="username"
                                       minlength="4"
                                       maxlength="100"
                                       required>
                            </div>
                        </div>

                        {{-- Mobile --}}
                        <div class="col-12 col-md-6">
                            <div class="customer-settings-form-group">
                                <label for="mobile" class="customer-settings-form-label">
                                    شماره موبایل
                                </label>

                                <input type="tel"
                                       id="mobile"
                                       name="mobile"
                                       value="{{ old('mobile', $user->mobile) }}"
                                       class="customer-settings-form-control"
                                       inputmode="numeric"
                                       maxlength="11"
                                       autocomplete="tel"
                                       required>
                            </div>
                        </div>

                    </div>

                    <div class="mt-2">
                        <button type="submit" class="customer-settings-submit">
                            <i class="bi bi-check-lg"></i>
                            ذخیره اطلاعات
                        </button>
                    </div>

                </form>
            </div>
        </div>

        {{-- Security + Notifications --}}
        <div class="row g-2">

            {{-- Security --}}
            <div class="col-12 col-md-6">
                <div class="card customer-settings-card h-100">
                    <div class="card-body">

                        <div class="customer-settings-card-header">
                            <div class="customer-settings-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>

                            <div>
                                <h2 class="customer-settings-card-title">
                                    امنیت حساب
                                </h2>
                                <span class="customer-settings-card-subtitle">
                                    تغییر رمز عبور
                                </span>
                            </div>
                        </div>

                        {{-- Password Success --}}
                        @if(session('success'))
                            <div class="alert alert-success customer-settings-alert">
                                <i class="bi bi-check-circle"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- Password Errors --}}
                        @if($errors->password->any())
                            <div class="alert alert-danger customer-settings-alert customer-settings-errors">
                                <ul class="mb-0">
                                    @foreach($errors->password->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST"
                              action="{{ route('customer.settings.password.update') }}"
                              novalidate
                              onsubmit="return validatePasswordForm(this)">

                            @csrf
                            @method('PUT')

                            <div class="row g-2">

                                {{-- Current Password --}}
                                <div class="col-12">
                                    <div class="customer-settings-form-group">
                                        <label for="current_password"
                                               class="customer-settings-form-label">
                                            رمز عبور فعلی
                                        </label>

                                        <div class="customer-settings-password-wrapper">
                                            <input type="password"
                                                   id="current_password"
                                                   name="current_password"
                                                   class="customer-settings-form-control customer-settings-password-input"
                                                   autocomplete="current-password"
                                                   required>

                                            <button type="button"
                                                    class="customer-settings-password-toggle"
                                                    aria-label="نمایش رمز عبور"
                                                    title="نمایش رمز عبور">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- New Password --}}
                                <div class="col-12 col-md-6">
                                    <div class="customer-settings-form-group">
                                        <label for="password"
                                               class="customer-settings-form-label">
                                            رمز عبور جدید
                                        </label>

                                        <div class="customer-settings-password-wrapper">
                                            <input type="password"
                                                   id="password"
                                                   name="password"
                                                   class="customer-settings-form-control customer-settings-password-input"
                                                   autocomplete="new-password"
                                                   minlength="8"
                                                   required>

                                            <button type="button"
                                                    class="customer-settings-password-toggle"
                                                    aria-label="نمایش رمز عبور"
                                                    title="نمایش رمز عبور">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>

                                        <small class="text-muted">
                                            رمز عبور باید حداقل ۸ کاراکتر باشد.
                                        </small>
                                    </div>
                                </div>

                                {{-- Password Confirmation --}}
                                <div class="col-12 col-md-6">
                                    <div class="customer-settings-form-group">
                                        <label for="password_confirmation"
                                               class="customer-settings-form-label">
                                            تکرار رمز عبور
                                        </label>

                                        <div class="customer-settings-password-wrapper">
                                            <input type="password"
                                                   id="password_confirmation"
                                                   name="password_confirmation"
                                                   class="customer-settings-form-control customer-settings-password-input"
                                                   autocomplete="new-password"
                                                   required>

                                            <button type="button"
                                                    class="customer-settings-password-toggle"
                                                    aria-label="نمایش رمز عبور"
                                                    title="نمایش رمز عبور">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="mt-2">
                                <button type="submit" class="customer-settings-submit">
                                    <i class="bi bi-check-lg"></i>
                                    تغییر رمز عبور
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            {{-- Notifications --}}
            <div class="col-12 col-md-6">
                <div class="card customer-settings-card h-100">
                    <div class="card-body">

                        <div class="customer-settings-card-header">
                            <div class="customer-settings-icon">
                                <i class="bi bi-bell"></i>
                            </div>

                            <div>
                                <h2 class="customer-settings-card-title">
                                    اعلان‌ها
                                </h2>
                                <span class="customer-settings-card-subtitle">
                                    اعلان‌های حساب
                                </span>
                            </div>
                        </div>

                        <a href="{{ route('customer.notifications.index') }}"
                           class="customer-settings-notification-link">

                            <div class="customer-settings-notification-content">
                                <i class="bi bi-bell"></i>
                                <span class="customer-settings-notification-text">
                                    مشاهده اعلان‌های من
                                </span>
                            </div>

                            <i class="bi bi-chevron-left customer-settings-notification-arrow"></i>
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function validateAccountForm(form) {
            const username = form.querySelector('[name="username"]');
            const mobile = form.querySelector('[name="mobile"]');

            username.setCustomValidity('');
            mobile.setCustomValidity('');

            if (!username.value.trim()) {
                username.setCustomValidity('لطفاً نام کاربری را وارد کنید.');
                username.reportValidity();
                return false;
            }

            if (username.value.trim().length < 4) {
                username.setCustomValidity('نام کاربری باید حداقل ۴ کاراکتر باشد.');
                username.reportValidity();
                return false;
            }

            if (!mobile.value.trim()) {
                mobile.setCustomValidity('لطفاً شماره موبایل را وارد کنید.');
                mobile.reportValidity();
                return false;
            }

            if (!/^[0-9]{11}$/.test(mobile.value.trim())) {
                mobile.setCustomValidity('شماره موبایل باید شامل ۱۱ رقم انگلیسی باشد.');
                mobile.reportValidity();
                return false;
            }

            return true;
        }

        function validatePasswordForm(form) {
            const currentPassword = form.querySelector('[name="current_password"]');
            const password = form.querySelector('[name="password"]');
            const confirmation = form.querySelector('[name="password_confirmation"]');

            [currentPassword, password, confirmation].forEach(input => {
                input.setCustomValidity('');
            });

            if (!currentPassword.value) {
                currentPassword.setCustomValidity('لطفاً رمز عبور فعلی را وارد کنید.');
                currentPassword.reportValidity();
                return false;
            }

            if (!password.value) {
                password.setCustomValidity('لطفاً رمز عبور جدید را وارد کنید.');
                password.reportValidity();
                return false;
            }

            if (password.value.length < 8) {
                password.setCustomValidity('رمز عبور جدید باید حداقل ۸ کاراکتر باشد.');
                password.reportValidity();
                return false;
            }

            if (!confirmation.value) {
                confirmation.setCustomValidity('لطفاً تکرار رمز عبور را وارد کنید.');
                confirmation.reportValidity();
                return false;
            }

            if (password.value !== confirmation.value) {
                confirmation.setCustomValidity('تکرار رمز عبور با رمز جدید مطابقت ندارد.');
                confirmation.reportValidity();
                return false;
            }

            return true;
        }

        document.querySelectorAll('.customer-settings input').forEach(input => {
            input.addEventListener('input', function () {
                this.setCustomValidity('');
            });
        });
    </script>

@endsection

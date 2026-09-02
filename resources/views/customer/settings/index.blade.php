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
                @if($errors->account->any()) <div class="alert alert-danger customer-settings-alert customer-settings-errors">


                    <ul class="mb-0">
                        @foreach($errors->account->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>


                @endif



                {{-- Account Form --}}
                <form method="POST"
                      action="{{ route('customer.settings.account.update') }}">

                    @csrf
                    @method('PUT')


                    <div class="row g-2">

                        {{-- Username --}}
                        <div class="col-12 col-md-6">

                            <div class="customer-settings-form-group">

                                <label class="customer-settings-form-label">
                                    نام کاربری
                                </label>

                                <input type="text"
                                       name="username"
                                       value="{{ old('username', $user->username) }}"
                                       class="customer-settings-form-control"
                                       autocomplete="username"
                                       required>

                            </div>

                        </div>


                        {{-- Mobile --}}
                        <div class="col-12 col-md-6">

                            <div class="customer-settings-form-group">

                                <label class="customer-settings-form-label">
                                    شماره موبایل
                                </label>

                                <input type="tel"
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

                        <button type="submit"
                                class="customer-settings-submit">

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
                        @if($errors->any() && !session('account_success'))
                            <div class="alert alert-danger customer-settings-alert customer-settings-errors">

                                <ul class="mb-0">

                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach

                                </ul>

                            </div>
                        @endif



                        <form method="POST"
                              action="{{ route('customer.settings.password.update') }}">

                            @csrf
                            @method('PUT')

                            <div class="row g-2">

                                {{-- Current Password --}}
                                <div class="col-12">

                                    <div class="customer-settings-form-group">

                                        <label class="customer-settings-form-label">
                                            رمز عبور فعلی
                                        </label>

                                        <div class="customer-settings-password-wrapper">

                                            <input type="password"
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

                                        <label class="customer-settings-form-label">
                                            رمز عبور جدید
                                        </label>

                                        <div class="customer-settings-password-wrapper">

                                            <input type="password"
                                                   name="password"
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

                                {{-- Password Confirmation --}}
                                <div class="col-12 col-md-6">

                                    <div class="customer-settings-form-group">

                                        <label class="customer-settings-form-label">
                                            تکرار رمز عبور
                                        </label>

                                        <div class="customer-settings-password-wrapper">

                                            <input type="password"
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

                                <button type="submit"
                                        class="customer-settings-submit">

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


@endsection

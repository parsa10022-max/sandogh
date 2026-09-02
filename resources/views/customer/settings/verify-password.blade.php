@extends('customer.layouts.app')

@section('title', 'تأیید تغییر رمز عبور')

@section('content')

    <div class="container-fluid px-0 customer-settings">

        <div class="customer-settings-header">

            <h1 class="customer-settings-title">
                <i class="bi bi-shield-lock"></i>
                تأیید تغییر رمز عبور
            </h1>

            <p class="customer-settings-description">
                برای تکمیل تغییر رمز عبور، کد تأیید ارسال‌شده به موبایل خود را وارد کنید.
            </p>

        </div>

        <div class="card customer-settings-card">

            <div class="card-body">

                <div class="customer-settings-card-header">

                    <div class="customer-settings-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>

                    <div>
                        <h2 class="customer-settings-card-title">
                            کد تأیید
                        </h2>

                        <span class="customer-settings-card-subtitle">
                            تأیید امنیتی
                        </span>
                    </div>

                </div>

                @if(session('password_otp_success'))

                    <div class="alert alert-success customer-settings-alert">

                        <i class="bi bi-check-circle"></i>

                        {{ session('password_otp_success') }}

                    </div>

                @endif

                @if($errors->otp->any())

                    <div class="alert alert-danger customer-settings-alert customer-settings-errors">

                        <ul class="mb-0">

                            @foreach($errors->otp->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif

                <div class="customer-settings-otp-mobile">

                    <span>
                        کد تأیید به شماره زیر ارسال شده است:
                    </span>

                    <strong>
                        {{ $user->mobile }}
                    </strong>

                </div>

                <form method="POST"
                      action="{{ route('customer.settings.password.verify.submit') }}">

                    @csrf

                    <div class="customer-settings-form-group">

                        <label class="customer-settings-form-label">
                            کد تأیید
                        </label>

                        <input
                            type="text"
                            name="code"
                            class="customer-settings-form-control customer-settings-otp-input"
                            inputmode="numeric"
                            maxlength="6"
                            autocomplete="one-time-code"
                            placeholder="کد ۶ رقمی"
                            required
                            autofocus
                        >

                    </div>

                    <div class="mt-2">

                        <button type="submit"
                                class="customer-settings-submit">

                            <i class="bi bi-check-lg"></i>

                            تأیید و تغییر رمز عبور

                        </button>

                    </div>

                </form>

                <div class="mt-2">

                    <a href="{{ route('customer.settings.index') }}"
                       class="customer-settings-back-link">

                        <i class="bi bi-arrow-right"></i>

                        بازگشت به تنظیمات

                    </a>

                </div>

            </div>

        </div>

    </div>

@endsection


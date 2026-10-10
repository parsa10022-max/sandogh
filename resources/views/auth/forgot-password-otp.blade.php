@extends('layouts.guest')

@section('title', 'تأیید کد بازیابی رمز')

@section('content')

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-12 col-sm-8 col-md-6 col-lg-4">

                <div class="card border-0 shadow-sm rounded-4">

                    <div class="card-body p-4">

                        <div class="text-center mb-4">

                            <div class="mb-3">
                                <i class="bi bi-shield-check fs-1 text-primary"></i>
                            </div>

                            <h5 class="fw-bold mb-2">
                                تأیید کد بازیابی
                            </h5>

                            <p class="text-muted small mb-0">
                                کد ۶ رقمی ارسال‌شده را وارد کنید.
                            </p>

                        </div>

                        @if(session('success'))
                            <div class="alert alert-success small">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger small">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('password.otp.verify') }}"
                            onsubmit="return validateOtpForm(this)"
                        >

                            @csrf

                            <div class="mb-3">
                                <label for="code" class="form-label">
                                    کد تأیید
                                </label>

                                <input
                                    type="text"
                                    id="code"
                                    name="code"
                                    value="{{ old('code') }}"
                                    class="form-control text-center @error('code') is-invalid @enderror"
                                    maxlength="6"
                                    inputmode="numeric"
                                    pattern="[0-9]{6}"
                                    autocomplete="one-time-code"
                                    placeholder="کد ۶ رقمی"
                                    aria-describedby="code-help"
                                    required
                                    autofocus
                                    oninvalid="this.setCustomValidity('لطفاً کد تأیید ۶ رقمی را وارد کنید.')"
                                    oninput="this.setCustomValidity('')"
                                >

                                <div id="code-help" class="form-text">
                                    کد تأیید باید شامل ۶ رقم باشد.
                                </div>

                                @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                <i class="bi bi-check-circle me-1"></i>
                                تأیید کد
                            </button>

                        </form>

                        @if(config('app.debug') && isset($testOtp) && $testOtp)
                            <div class="alert alert-warning mt-3 mb-0 small text-center">

                                کد تست:
                                <strong>{{ $testOtp }}</strong>

                            </div>
                        @endif

                        <div class="text-center mt-3">

                            <a
                                href="{{ route('password.request') }}"
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
    <script>
        function validateOtpForm(form) {
            const code = form.querySelector('#code');

            if (!code.value.trim()) {
                code.setCustomValidity('لطفاً کد تأیید ۶ رقمی را وارد کنید.');
                code.reportValidity();
                return false;
            }

            if (!/^[0-9]{6}$/.test(code.value)) {
                code.setCustomValidity('کد تأیید باید شامل ۶ رقم انگلیسی باشد.');
                code.reportValidity();
                return false;
            }

            code.setCustomValidity('');
            return true;
        }
    </script>
@endsection

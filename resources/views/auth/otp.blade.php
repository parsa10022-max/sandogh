@extends('layouts.guest')

@section('title', 'تأیید شماره موبایل')

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
                                تأیید شماره موبایل
                            </h5>

                            <p class="text-muted small mb-0">
                                کد تأیید ارسال‌شده به شماره موبایل خود را وارد کنید.
                            </p>

                        </div>

                        @if ($errors->any())

                            <div class="alert alert-danger small">

                                {{ $errors->first() }}

                            </div>

                        @endif


                        @if(config('app.debug') && $testOtp)
                            <div class="alert alert-warning small">
                                <strong>
                                    کد تست OTP:
                                </strong>

                                <span class="fs-5 fw-bold">
            {{ $testOtp }}
        </span>
                            </div>
                        @endif


                        <form method="POST" action="{{ route('otp.verify') }}">

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
                                    oninvalid="this.setCustomValidity('لطفاً کد تأیید ۶ رقمی را وارد کنید.')"
                                    oninput="this.setCustomValidity('')"
                                    autofocus
                                >

                                <div id="code-help" class="form-text">
                                    کد ۶ رقمی ارسال‌شده را وارد کنید.
                                </div>

                                @error('code')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
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




                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection


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
                        >

                            @csrf

                            <div class="mb-3">

                                <label
                                    for="code"
                                    class="form-label"
                                >
                                    کد تأیید
                                </label>

                                <input
                                    type="text"
                                    id="code"
                                    name="code"
                                    class="form-control text-center"
                                    maxlength="6"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    required
                                    autofocus
                                >

                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                <i class="bi bi-check-circle me-1"></i>
                                تأیید کد
                            </button>

                        </form>

                        @if(config('app.debug') && $otp)

                            <div class="alert alert-warning mt-3 mb-0 small text-center">

                                کد تست:
                                <strong>{{ $otp->code }}</strong>

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

@endsection

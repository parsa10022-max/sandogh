@extends('layouts.guest')

@section('title', 'فراموشی رمز عبور')

@section('content')

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-12 col-sm-8 col-md-6 col-lg-4">

                <div class="card border-0 shadow-sm rounded-4">

                    <div class="card-body p-4">

                        <div class="text-center mb-4">

                            <div class="mb-3">
                                <i class="bi bi-key-fill fs-1 text-primary"></i>
                            </div>

                            <h5 class="fw-bold mb-2">
                                فراموشی رمز عبور
                            </h5>

                            <p class="text-muted small mb-0">
                                شماره موبایل خود را وارد کنید تا کد تأیید برای شما ارسال شود.
                            </p>

                        </div>

                        @if($errors->any())

                            <div class="alert alert-danger small">
                                {{ $errors->first() }}
                            </div>

                        @endif

                        <form
                            method="POST"
                            action="{{ route('password.otp.send') }}"
                        >

                            @csrf

                            <div class="mb-3">

                                <label
                                    for="mobile"
                                    class="form-label"
                                >
                                    شماره موبایل
                                </label>

                                <input
                                    type="text"
                                    id="mobile"
                                    name="mobile"
                                    value="{{ old('mobile') }}"
                                    class="form-control"
                                    placeholder="مثلاً 09123456789"
                                    maxlength="20"
                                    inputmode="numeric"
                                    required
                                    autofocus
                                >

                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                <i class="bi bi-send me-1"></i>
                                ارسال کد تأیید
                            </button>

                        </form>

                        <div class="text-center mt-3">

                            <a
                                href="{{ route('login') }}"
                                class="text-decoration-none small"
                            >
                                بازگشت به صفحه ورود
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection

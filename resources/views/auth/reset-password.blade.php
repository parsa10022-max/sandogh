@extends('layouts.guest')

@section('title', 'تعیین رمز عبور جدید')

@section('content')

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-12 col-sm-10 col-md-6 col-lg-4">

                <div class="card border-0 shadow-sm rounded-4">

                    <div class="card-body p-4">

                        <div class="text-center mb-4">

                            <div class="mb-3">
                                <i class="bi bi-shield-lock fs-1 text-primary"></i>
                            </div>

                            <h4 class="fw-bold mb-2">
                                تعیین رمز عبور جدید
                            </h4>

                            <p class="text-muted small mb-0">
                                رمز عبور جدید خود را وارد کنید.
                            </p>

                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('password.reset.update') }}"
                        >
                            @csrf
                            @method('PUT')

                            <div class="mb-3">

                                <label
                                    for="password"
                                    class="form-label fw-semibold"
                                >
                                    رمز عبور جدید
                                </label>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    minlength="8"
                                    required
                                    autofocus
                                >

                            </div>

                            <div class="mb-4">

                                <label
                                    for="password_confirmation"
                                    class="form-label fw-semibold"
                                >
                                    تکرار رمز عبور جدید
                                </label>

                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-control"
                                    minlength="8"
                                    required
                                >

                                <div class="form-text">
                                    رمز عبور باید حداقل ۸ کاراکتر باشد.
                                </div>

                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                <i class="bi bi-check-circle me-1"></i>
                                ذخیره رمز جدید
                            </button>

                        </form>

                        <div class="text-center mt-3">

                            <a
                                href="{{ route('login') }}"
                                class="text-decoration-none"
                            >
                                بازگشت به ورود
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection

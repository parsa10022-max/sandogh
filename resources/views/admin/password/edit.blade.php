@extends('layouts.app')

@section('title', 'تغییر رمز عبور')

@section('content')

    <div class="container-fluid">

        {{-- عنوان صفحه --}}
        <div class="mb-4">

            <h4 class="fw-bold mb-1">
                تغییر رمز عبور
            </h4>

            <p class="text-muted mb-0">
                برای امنیت حساب کاربری، رمز عبور خود را تغییر دهید.
            </p>

        </div>


        {{-- پیام موفقیت --}}
        @if(session('success'))

            <div class="alert alert-success d-flex align-items-center gap-2 mb-4">

                <i class="bi bi-check-circle-fill"></i>

                <span>
                    {{ session('success') }}
                </span>

            </div>

        @endif


        {{-- خطاها --}}
        @if($errors->any())

            <div class="alert alert-danger mb-4">

                <div class="fw-bold mb-2">
                    لطفاً خطاهای زیر را بررسی کنید:
                </div>

                <ul class="mb-0">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <div class="row">

            <div class="col-12 col-lg-7 col-xl-6">

                <div class="card border rounded-4 shadow-sm">

                    <div class="card-header bg-white border-bottom rounded-top-4 py-3">

                        <div class="d-flex align-items-center gap-3">

                            <div
                                class="d-flex align-items-center justify-content-center rounded-3"
                                style="
                                    width: 44px;
                                    height: 44px;
                                    background: rgba(111, 66, 193, .10);
                                    color: #6f42c1;
                                "
                            >
                                <i class="bi bi-key fs-5"></i>
                            </div>

                            <div>

                                <h5 class="fw-bold mb-1">
                                    تغییر رمز عبور
                                </h5>

                                <small class="text-muted">
                                    رمز فعلی و رمز جدید خود را وارد کنید.
                                </small>

                            </div>

                        </div>

                    </div>


                    <div class="card-body p-4">

                        <form
                            method="POST"
                            action="{{ route('admin.password.update') }}"
                        >

                            @csrf
                            @method('PUT')


                            {{-- رمز فعلی --}}
                            <div class="mb-3">

                                <label
                                    for="current_password"
                                    class="form-label fw-semibold"
                                >
                                    رمز عبور فعلی
                                </label>

                                <input
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    class="form-control"
                                    autocomplete="current-password"
                                    required
                                >

                                @error('current_password')

                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>

                                @enderror

                            </div>


                            {{-- رمز جدید --}}
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
                                    autocomplete="new-password"
                                    required
                                >

                                <div class="form-text">
                                    رمز عبور باید حداقل ۸ کاراکتر باشد.
                                </div>

                                @error('password')

                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>

                                @enderror

                            </div>


                            {{-- تکرار رمز جدید --}}
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
                                    autocomplete="new-password"
                                    required
                                >

                            </div>


                            <div class="d-flex justify-content-end">

                                <button
                                    type="submit"
                                    class="btn btn-primary px-4"
                                >
                                    <i class="bi bi-shield-lock me-1"></i>
                                    تغییر رمز عبور
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection

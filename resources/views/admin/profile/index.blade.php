@extends('layouts.app')

@section('title', 'پروفایل مدیر')

@section('content')

<div class="container-fluid">

    {{-- عنوان صفحه --}}
    <div class="mb-4">

        <h4 class="fw-bold mb-1">
            پروفایل مدیر
        </h4>

        <p class="text-muted mb-0">
            اطلاعات حساب کاربری خود را مدیریت کنید.
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


    {{-- خطاهای اعتبارسنجی --}}
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


    <div class="row g-4">

        {{-- اطلاعات حساب --}}
        <div class="col-12 col-lg-8">

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
                            <i class="bi bi-person fs-5"></i>
                        </div>

                        <div>

                            <h5 class="fw-bold mb-1">
                                اطلاعات حساب
                            </h5>

                            <small class="text-muted">
                                اطلاعات قابل ویرایش حساب کاربری
                            </small>

                        </div>

                    </div>

                </div>


                <div class="card-body p-4">

                    <form
                        method="POST"
                        action="{{ route('admin.profile.update') }}"
                    >

                        @csrf
                        @method('PUT')


                        <div class="row g-3">

                            {{-- نام کاربری --}}
                            <div class="col-12">

                                <label
                                    for="username"
                                    class="form-label fw-semibold"
                                >
                                    نام کاربری
                                </label>

                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    class="form-control"
                                    value="{{ old('username', $user->username) }}"
                                    required
                                >

                                @error('username')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>


                            {{-- موبایل --}}
                            <div class="col-12 col-md-6">

                                <label
                                    for="mobile"
                                    class="form-label fw-semibold"
                                >
                                    شماره موبایل
                                </label>

                                <input
                                    type="text"
                                    id="mobile"
                                    name="mobile"
                                    class="form-control"
                                    value="{{ old('mobile', $user->mobile) }}"
                                >

                                @error('mobile')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>


                            {{-- ایمیل --}}
                            <div class="col-12 col-md-6">

                                <label
                                    for="email"
                                    class="form-label fw-semibold"
                                >
                                    ایمیل
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    value="{{ old('email', $user->email) }}"
                                >

                                @error('email')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>

                        </div>


                        <div class="d-flex justify-content-end mt-4">

                            <button
                                type="submit"
                                class="btn btn-primary px-4"
                            >
                                <i class="bi bi-check2-circle me-1"></i>
                                ذخیره تغییرات
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>


        {{-- اطلاعات دسترسی --}}
        <div class="col-12 col-lg-4">

            <div class="card border rounded-4 shadow-sm">

                <div class="card-header bg-white border-bottom rounded-top-4 py-3">

                    <div class="d-flex align-items-center gap-3">

                        <div
                            class="d-flex align-items-center justify-content-center rounded-3"
                            style="
                                    width: 44px;
                                    height: 44px;
                                    background: rgba(13, 110, 253, .10);
                                    color: #0d6efd;
                                "
                        >
                            <i class="bi bi-shield-check fs-5"></i>
                        </div>

                        <div>

                            <h5 class="fw-bold mb-1">
                                اطلاعات دسترسی
                            </h5>

                            <small class="text-muted">
                                وضعیت حساب کاربری
                            </small>

                        </div>

                    </div>

                </div>


                <div class="card-body p-4">

                    <div class="mb-3">

                        <small class="text-muted d-block mb-1">
                            نقش کاربر
                        </small>

                        <div class="fw-semibold">
                            {{ $user->role->label() }}
                        </div>

                    </div>


                    <div>

                        <small class="text-muted d-block mb-1">
                            وضعیت حساب
                        </small>

                        <div>

                            @if($user->status)

                            <span class="badge bg-success-subtle text-success">
                                        فعال
                                    </span>

                            @else

                            <span class="badge bg-danger-subtle text-danger">
                                        غیرفعال
                                    </span>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

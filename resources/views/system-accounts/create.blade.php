@extends('layouts.app')

@section('title', 'ایجاد حساب سیستمی')

@push('styles')
    @vite('resources/css/admin/system-accounts/create.css')
@endpush

@section('content')

    <div class="container-fluid system-account-create-page">

        {{-- Header --}}
        <div class="system-account-create-header">

            <div class="system-account-create-header__content">

                <div class="system-account-create-header__icon">
                    <i class="bi bi-bank"></i>
                </div>

                <div>

                    <h1 class="system-account-create-header__title">
                        ایجاد حساب سیستمی
                    </h1>

                    <p class="system-account-create-header__subtitle mb-0">
                        ثبت یک حساب جدید برای مدیریت مالی صندوق
                    </p>

                </div>

            </div>

            <a href="{{ route('system-accounts.index') }}"
               class="system-account-create-back-btn">

                <i class="bi bi-arrow-right"></i>

                <span>
                    بازگشت
                </span>

            </a>

        </div>


        {{-- Form Card --}}
        <div class="system-account-create-card">

            <div class="system-account-create-card__header">

                <div class="system-account-create-card__title-wrapper">

                    <div class="system-account-create-card__icon">
                        <i class="bi bi-wallet2"></i>
                    </div>

                    <div>

                        <h2 class="system-account-create-card__title">
                            اطلاعات حساب
                        </h2>

                        <p class="system-account-create-card__subtitle mb-0">
                            اطلاعات حساب سیستمی جدید را وارد کنید.
                        </p>

                    </div>

                </div>

            </div>


            <div class="system-account-create-card__body">

                <form method="POST"
                      action="{{ route('system-accounts.store') }}">

                    @csrf


                    {{-- Account Name --}}
                    <div class="system-account-form-group">

                        <label for="name"
                               class="system-account-form-label">

                            نام حساب

                            <span class="system-account-required">
                                *
                            </span>

                        </label>

                        <div class="system-account-input-wrapper">

                            <span class="system-account-input-icon">
                                <i class="bi bi-wallet2"></i>
                            </span>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="system-account-form-input @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                placeholder="مثلاً کمک‌های مردمی"
                                autocomplete="off"
                                required
                            >

                        </div>

                        @error('name')
                        <div class="system-account-error">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>


                    {{-- Account Number --}}
                    <div class="system-account-form-group">

                        <label for="account_number"
                               class="system-account-form-label">

                            شماره حساب

                            <span class="system-account-required">
                                *
                            </span>

                        </label>

                        <div class="system-account-input-wrapper">

                            <span class="system-account-input-icon">
                                <i class="bi bi-credit-card"></i>
                            </span>

                            <input
                                type="text"
                                id="account_number"
                                name="account_number"
                                class="system-account-form-input system-account-form-input--ltr @error('account_number') is-invalid @enderror"
                                value="{{ old('account_number') }}"
                                placeholder="شماره حساب"
                                inputmode="numeric"
                                autocomplete="off"
                                required
                            >

                        </div>

                        @error('account_number')
                        <div class="system-account-error">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>


                    {{-- Actions --}}
                    <div class="system-account-form-actions">

                        <a href="{{ route('system-accounts.index') }}"
                           class="system-account-cancel-btn">

                            <i class="bi bi-x-circle"></i>

                            <span>
                                انصراف
                            </span>

                        </a>

                        <button type="submit"
                                class="system-account-submit-btn">

                            <i class="bi bi-check-circle"></i>

                            <span>
                                ذخیره حساب
                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection


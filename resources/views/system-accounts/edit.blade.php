@extends('layouts.app')

@section('title', 'ویرایش حساب سیستمی')

@push('styles')
    @vite('resources/css/admin/system-accounts/edit.css')
@endpush

@section('content')

    <div class="system-account-edit-page">

        {{-- Header --}}
        <div class="system-account-edit-header">

            <div class="system-account-edit-header__content">

                <div class="system-account-edit-header__icon">
                    <i class="bi bi-pencil-square"></i>
                </div>

                <div>
                    <h1 class="system-account-edit-header__title">
                        ویرایش حساب سیستمی
                    </h1>

                    <p class="system-account-edit-header__subtitle">
                        اطلاعات حساب سیستمی را ویرایش کنید.
                    </p>
                </div>

            </div>

            <a href="{{ route('system-accounts.index') }}"
               class="system-account-edit-back">

                <i class="bi bi-arrow-right"></i>

                <span>بازگشت</span>

            </a>

        </div>


        {{-- Form Card --}}
        <div class="system-account-edit-card">

            <div class="system-account-edit-card__header">

                <div class="system-account-edit-card__icon">
                    <i class="bi bi-bank"></i>
                </div>

                <div>

                    <h2 class="system-account-edit-card__title">
                        اطلاعات حساب
                    </h2>

                    <p class="system-account-edit-card__subtitle">
                        نام حساب را می‌توانید تغییر دهید.
                    </p>

                </div>

            </div>


            <div class="system-account-edit-card__body">

                <form method="POST"
                      action="{{ route(
                      'system-accounts.update',
                      $systemAccount
                  ) }}">

                    @csrf
                    @method('PUT')


                    {{-- نام حساب --}}
                    <div class="system-account-edit-field">

                        <label for="name"
                               class="system-account-edit-label">

                            نام حساب

                            <span class="system-account-edit-required">
                            *
                        </span>

                        </label>

                        <div class="system-account-edit-input-wrapper">

                        <span class="system-account-edit-input-icon">
                            <i class="bi bi-card-text"></i>
                        </span>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="system-account-edit-input
                                @error('name') is-invalid @enderror"
                                value="{{ old(
                                'name',
                                $systemAccount->name
                            ) }}"
                                placeholder="مثلاً کمک‌های مردمی"
                                autocomplete="off"
                                required
                            >

                        </div>

                        @error('name')
                        <div class="system-account-edit-error">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                        @enderror

                    </div>


                    {{-- شماره حساب --}}
                    <div class="system-account-edit-field">

                        <label for="account_number"
                               class="system-account-edit-label">

                            شماره حساب

                        </label>

                        <div class="system-account-edit-input-wrapper">

                        <span class="system-account-edit-input-icon">
                            <i class="bi bi-hash"></i>
                        </span>

                            <input
                                type="text"
                                id="account_number"
                                class="system-account-edit-input
                                system-account-edit-input--readonly"
                                value="{{ $systemAccount->account_number }}"
                                readonly
                                dir="ltr"
                            >

                        </div>

                        <div class="system-account-edit-hint">

                            <i class="bi bi-info-circle"></i>

                            شماره حساب قابل ویرایش نیست.

                        </div>

                    </div>


                    {{-- Actions --}}
                    <div class="system-account-edit-actions">

                        <a href="{{ route('system-accounts.index') }}"
                           class="system-account-edit-btn
                              system-account-edit-btn--secondary">

                            <i class="bi bi-x-lg"></i>

                            انصراف

                        </a>


                        <button type="submit"
                                class="system-account-edit-btn
                                   system-account-edit-btn--primary">

                            <i class="bi bi-check2-circle"></i>

                            ذخیره تغییرات

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection

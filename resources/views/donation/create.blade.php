@extends('layouts.app')

@section('title', 'کمک به صندوق')



@section('content')

    <div class="donation-page">

        {{-- Header --}}
        <div class="donation-header">

            <div class="donation-header__content">

                <div class="donation-header__icon">
                    <i class="bi bi-heart-fill"></i>
                </div>

                <div>
                    <h1 class="donation-header__title">
                        کمک به صندوق
                    </h1>

                    <p class="donation-header__subtitle">
                        با انتخاب حساب و وارد کردن مبلغ، کمک خود را ثبت کنید.
                    </p>
                </div>

            </div>

        </div>


        {{-- Main Card --}}
        <div class="donation-card">

            <div class="donation-card__header">

                <div class="donation-card__icon">
                    <i class="bi bi-heart-fill"></i>
                </div>

                <div>
                    <h2 class="donation-card__title">
                        ثبت کمک
                    </h2>

                    <p class="donation-card__subtitle">
                        اطلاعات پرداخت را وارد کنید.
                    </p>
                </div>

            </div>


            <div class="donation-card__body">

                {{-- Success --}}
                @if(session('success'))

                    <div class="donation-alert donation-alert--success">

                        <i class="bi bi-check-circle-fill"></i>

                        <span>
                        {{ session('success') }}
                    </span>

                    </div>

                @endif


                {{-- Errors --}}
                @if($errors->any())

                    <div class="donation-alert donation-alert--danger">

                        <div class="donation-alert__icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>

                        <div>

                            <div class="donation-alert__title">
                                لطفاً موارد زیر را بررسی کنید:
                            </div>

                            <ul class="donation-alert__list">

                                @foreach($errors->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    </div>

                @endif


                <form method="POST"
                      action="{{ route('donation.store') }}">

                    @csrf


                    {{-- Account --}}
                    <div class="donation-field donation-account-field">

                        <label class="donation-label">

                            انتخاب حساب صندوق

                            <span class="donation-required">*</span>

                        </label>

                        <div class="donation-account-grid">

                            @foreach($accounts as $account)

                                <div class="donation-account-option">

                                    <input
                                        type="radio"
                                        class="btn-check"
                                        name="account_id"
                                        id="account{{ $account->id }}"
                                        value="{{ $account->id }}"
                                        @checked(old('account_id') == $account->id)
                                    required
                                    >

                                    <label
                                        class="donation-account-card"
                                        for="account{{ $account->id }}"
                                    >

                                        <div class="donation-account-card__top">

                                            <div class="donation-account-card__icon">

                                                <i class="bi bi-bank"></i>

                                            </div>

                                            <div class="donation-account-card__content">

                                                <h3 class="donation-account-card__name">
                                                    {{ $account->name }}
                                                </h3>

                                                <div class="donation-account-card__number">

                                                <span>
                                                    شماره حساب:
                                                </span>

                                                    <strong dir="ltr">
                                                        {{ $account->account_number }}
                                                    </strong>

                                                </div>

                                            </div>

                                        </div>


                                        <div class="donation-account-card__status">

                                        <span class="donation-status donation-status--active">

                                            <span class="donation-status__dot"></span>

                                            فعال

                                        </span>

                                            <span class="donation-selected-badge">

                                            <i class="bi bi-check-circle-fill"></i>

                                            انتخاب شد

                                        </span>

                                        </div>

                                    </label>

                                </div>

                            @endforeach

                        </div>

                        @error('account_id')

                        <div class="donation-error">

                            <i class="bi bi-exclamation-circle"></i>

                            {{ $message }}

                        </div>

                        @enderror

                    </div>


                    {{-- Donor Name --}}
                    <div class="donation-field">

                        <label for="donor_name"
                               class="donation-label">

                            نام پرداخت کننده

                        </label>

                        <div class="donation-input-wrapper">

                        <span class="donation-input-icon">
                            <i class="bi bi-person"></i>
                        </span>

                            <input
                                type="text"
                                id="donor_name"
                                name="donor_name"
                                class="donation-input
                                @error('donor_name') is-invalid @enderror"
                                value="{{ old('donor_name') }}"
                                placeholder="نام و نام خانوادگی"
                                autocomplete="name"
                            >

                        </div>

                        @error('donor_name')

                        <div class="donation-error">

                            <i class="bi bi-exclamation-circle"></i>

                            {{ $message }}

                        </div>

                        @enderror

                    </div>


                    {{-- Mobile --}}
                    <div class="donation-field">

                        <label for="donor_mobile"
                               class="donation-label">

                            شماره موبایل

                        </label>

                        <div class="donation-input-wrapper">

                        <span class="donation-input-icon">
                            <i class="bi bi-phone"></i>
                        </span>

                            <input
                                type="text"
                                id="donor_mobile"
                                name="donor_mobile"
                                class="donation-input
                                @error('donor_mobile') is-invalid @enderror"
                                value="{{ old('donor_mobile') }}"
                                placeholder="مثلاً ۰۹۱۲۱۲۳۴۵۶۷"
                                inputmode="tel"
                                autocomplete="tel"
                                dir="ltr"
                            >

                        </div>

                        @error('donor_mobile')

                        <div class="donation-error">

                            <i class="bi bi-exclamation-circle"></i>

                            {{ $message }}

                        </div>

                        @enderror

                    </div>


                    {{-- Amount --}}
                    <div class="donation-field">

                        <label for="amount"
                               class="donation-label">

                            مبلغ

                            <span class="donation-required">*</span>

                        </label>

                        <div class="donation-input-wrapper donation-input-wrapper--amount">

                        <span class="donation-input-icon">
                            <i class="bi bi-cash-stack"></i>
                        </span>

                            <input
                                type="text"
                                id="amount"
                                name="amount"
                                class="donation-input money-input
                                @error('amount') is-invalid @enderror"
                                value="{{ old('amount') }}"
                                placeholder="مبلغ را وارد کنید"
                                inputmode="numeric"
                                autocomplete="off"
                                data-min="10000"
                                required
                            >

                            <span class="donation-input-unit">
                            ریال
                        </span>

                        </div>

                        <div class="donation-field-hint">

                            <i class="bi bi-info-circle"></i>

                            حداقل مبلغ کمک ۱۰٬۰۰۰ ریال است.

                        </div>

                        @error('amount')

                        <div class="donation-error">

                            <i class="bi bi-exclamation-circle"></i>

                            {{ $message }}

                        </div>

                        @enderror

                    </div>


                    {{-- Submit --}}
                    <div class="donation-actions">

                        <button type="submit"
                                class="donation-submit-btn">

                            <i class="bi bi-credit-card"></i>

                            ادامه پرداخت

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection

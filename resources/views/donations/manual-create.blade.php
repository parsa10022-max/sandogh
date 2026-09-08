@extends('layouts.app')

@section('title', 'ثبت کمک دستی')



@section('content')


    <div class="donation-manual-page">

        {{-- Header --}}
        <div class="donation-manual-header">

            <div class="donation-manual-header__content">

                <div class="donation-manual-header__icon">
                    <i class="bi bi-hand-heart-fill"></i>
                </div>

                <div>

                    <h1 class="donation-manual-header__title">
                        ثبت کمک دستی
                    </h1>

                    <p class="donation-manual-header__subtitle">
                        ثبت کمک‌هایی که خارج از درگاه پرداخت دریافت شده‌اند.
                    </p>

                </div>

            </div>

        </div>


        {{-- Card --}}
        <div class="donation-manual-card">

            <div class="donation-manual-card__header">

                <div class="donation-manual-card__icon">
                    <i class="bi bi-plus-circle"></i>
                </div>

                <div>

                    <h2 class="donation-manual-card__title">
                        اطلاعات کمک
                    </h2>

                    <p class="donation-manual-card__subtitle">
                        حساب مقصد و مبلغ کمک را مشخص کنید.
                    </p>

                </div>

            </div>


            <div class="donation-manual-card__body">

                {{-- Success --}}
                @if(session('success'))

                    <div class="donation-manual-alert donation-manual-alert--success">

                        <i class="bi bi-check-circle-fill"></i>

                        <span>
                        {{ session('success') }}
                    </span>

                    </div>

                @endif


                {{-- Errors --}}
                @if($errors->any())

                    <div class="donation-manual-alert donation-manual-alert--danger">

                        <div class="donation-manual-alert__icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>

                        <div>

                            <div class="donation-manual-alert__title">
                                لطفاً موارد زیر را بررسی کنید:
                            </div>

                            <ul class="donation-manual-alert__list">

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
                      action="{{ route('donations.manual.store') }}">

                    @csrf


                    {{-- Account --}}
                    <div class="donation-manual-field">

                        <label for="account_id"
                               class="donation-manual-label">

                            حساب مقصد

                            <span class="donation-manual-required">
                            *
                        </span>

                        </label>

                        <div class="donation-manual-input-wrapper">

                        <span class="donation-manual-input-icon">
                            <i class="bi bi-bank"></i>
                        </span>

                            <select
                                id="account_id"
                                name="account_id"
                                class="donation-manual-select
                                @error('account_id') is-invalid @enderror"
                                required
                            >

                                <option value="">
                                    انتخاب حساب
                                </option>

                                @foreach($accounts as $account)

                                    <option
                                        value="{{ $account->id }}"
                                        @selected(old('account_id') == $account->id)
                                    >
                                    {{ $account->name }}
                                    -
                                    {{ $account->account_number }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        @error('account_id')

                        <div class="donation-manual-error">

                            <i class="bi bi-exclamation-circle"></i>

                            {{ $message }}

                        </div>

                        @enderror

                    </div>


                    {{-- Amount --}}
                    <div class="donation-manual-field">

                        <label for="amount"
                               class="donation-manual-label">

                            مبلغ

                            <span class="donation-manual-required">
                            *
                        </span>

                        </label>

                        <div class="donation-manual-input-wrapper
                                donation-manual-input-wrapper--amount">

                        <span class="donation-manual-input-icon">
                            <i class="bi bi-cash-stack"></i>
                        </span>

                            <input
                                type="text"
                                id="amount"
                                name="amount"
                                class="donation-manual-input money-input
                                @error('amount') is-invalid @enderror"
                                value="{{ old('amount') }}"
                                placeholder="مبلغ کمک را وارد کنید"
                                inputmode="numeric"
                                autocomplete="off"
                                data-min="1000"
                                required
                            >

                            <span class="donation-manual-input-unit">
                            ریال
                        </span>

                        </div>

                        <div class="donation-manual-hint">

                            <i class="bi bi-info-circle"></i>

                            حداقل مبلغ کمک ۱٬۰۰۰ ریال است.

                        </div>

                        @error('amount')

                        <div class="donation-manual-error">

                            <i class="bi bi-exclamation-circle"></i>

                            {{ $message }}

                        </div>

                        @enderror

                    </div>


                    {{-- Actions --}}
                    <div class="donation-manual-actions">

                        <a href="{{ route('donations.index') }}"
                           class="donation-manual-btn
                              donation-manual-btn--secondary">

                            <i class="bi bi-arrow-right"></i>

                            بازگشت

                        </a>

                        <button type="submit"
                                class="donation-manual-btn
                                   donation-manual-btn--primary">

                            <i class="bi bi-check2-circle"></i>

                            ثبت کمک

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection

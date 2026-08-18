@extends('customer.layouts.app')

@section('title', 'برداشت از حساب پس‌انداز')

@section('header_title', 'برداشت از حساب پس‌انداز')

@section('header_subtitle', 'برداشت وجه از حساب پس‌انداز شما')

@push('styles')
    @vite('resources/css/customer/savings-withdrawal.css')
@endpush

@section('content')

    <div class="customer-savings-withdrawal-page">

        <div class="customer-savings-withdrawal-card">

            {{-- =====================================================
                 CARD HEADER
            ====================================================== --}}

            <div class="customer-savings-withdrawal-header">

                <div class="customer-savings-withdrawal-header-icon">

                    <i class="bi bi-arrow-return-left"></i>

                </div>

                <div class="customer-savings-withdrawal-header-content">

                    <h1>
                        برداشت از حساب پس‌انداز
                    </h1>

                    <span>
                        برداشت وجه از حساب پس‌انداز
                    </span>

                </div>

            </div>


            {{-- =====================================================
                 ACCOUNT INFORMATION
            ====================================================== --}}

            <div class="customer-savings-withdrawal-account">

                {{-- صاحب حساب --}}
                <div class="customer-savings-withdrawal-account-item">

                    <span>
                        صاحب حساب
                    </span>

                    <strong>
                        {{ $account->customer->full_name }}
                    </strong>

                </div>


                {{-- شماره حساب --}}
                <div class="customer-savings-withdrawal-account-item">

                    <span>
                        شماره حساب
                    </span>

                    <strong dir="ltr">
                        {{ $account->account_number }}
                    </strong>

                </div>


                {{-- موجودی --}}
                <div class="customer-savings-withdrawal-account-item balance">

                    <span>
                        موجودی فعلی
                    </span>

                    <strong>
                        {{ number_format($account->balance) }}

                        <small>
                            ریال
                        </small>

                    </strong>

                </div>

            </div>


            {{-- =====================================================
                 FORM
            ====================================================== --}}

            <form
                method="POST"
                action="{{ route('customer.savings.withdrawal.store') }}"
                class="customer-savings-withdrawal-form"
            >

                @csrf


                {{-- =================================================
                     IBAN ALERT
                ================================================== --}}
                @php

                    $iban = strtoupper(
                        trim($customer->iban ?? '')
                    );

                    $iban = preg_replace(
                        '/\s+/',
                        '',
                        $iban
                    );

                    $ibanFormatted = trim(
                        chunk_split(
                            $iban,
                            4,
                            ' '
                        )
                    );

                @endphp


                <div class="customer-savings-withdrawal-iban-alert">

                    <i class="bi bi-bank"></i>

                    <div class="customer-savings-withdrawal-iban-content">

        <span class="customer-savings-withdrawal-iban-title">
            حساب مقصد برداشت
        </span>


                        @if($iban)

                            <strong
                                dir="ltr"
                                class="customer-savings-withdrawal-iban-number"
                            >
                                {{ $ibanFormatted }}
                            </strong>

                        @else

                            <strong class="customer-savings-withdrawal-iban-empty">
                                شماره شبا ثبت نشده است
                            </strong>

                        @endif


                        <div class="customer-savings-withdrawal-iban-name">

                            <i class="bi bi-person-fill"></i>

                            <span>
                به نام:
            </span>

                            <strong>
                                {{ $customer->full_name }}
                            </strong>

                        </div>


                        <small class="customer-savings-withdrawal-iban-owner">

                            مبلغ برداشت فقط به شماره شبای ثبت‌شده
                            و به نام خود مشتری واریز می‌شود.

                        </small>

                    </div>

                </div>


                {{-- =================================================
                     AMOUNT
                ================================================== --}}

                <div class="customer-savings-withdrawal-field">

                    <label
                        for="amount"
                        class="customer-savings-withdrawal-label"
                    >
                        مبلغ برداشت
                    </label>


                    <div class="customer-savings-withdrawal-money">

                        <input
                            type="text"
                            name="amount"
                            id="amount"
                            class="money-input @error('amount') is-invalid @enderror"
                            value="{{ old('amount') }}"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="مثلاً 500,000"
                            data-min="500000"
                            required
                        >

                        <span class="rial-box">
                            ریال
                        </span>

                    </div>


                    @error('amount')

                    <div class="customer-savings-withdrawal-error">

                        {{ $message }}

                    </div>

                    @enderror


                    <div class="customer-savings-withdrawal-help">

                        <i class="bi bi-info-circle"></i>

                        حداقل مبلغ برداشت

                        <strong>
                            ۵۰۰,۰۰۰ ریال
                        </strong>

                        است.

                    </div>

                </div>


                {{-- =================================================
                     ACTION
                ================================================== --}}

                <div class="customer-savings-withdrawal-actions">

                    <button
                        type="submit"
                        class="customer-savings-withdrawal-submit"
                    >

                        <i class="bi bi-arrow-down-circle"></i>

                        <span>
                            ثبت درخواست برداشت
                        </span>

                        <i class="bi bi-arrow-left"></i>

                    </button>

                </div>

            </form>


            {{-- =====================================================
                 SECURITY NOTE
            ====================================================== --}}

            <div class="customer-savings-withdrawal-note">

                <i class="bi bi-shield-check"></i>

                <span>
                    درخواست برداشت شما پس از ثبت، در سوابق حساب ذخیره می‌شود.
                </span>

            </div>

        </div>

    </div>

@endsection

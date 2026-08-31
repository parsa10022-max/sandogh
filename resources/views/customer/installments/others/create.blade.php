@extends('customer.layouts.app')

@section('title', 'پرداخت اقساط دیگران')
@section('header_title', 'پرداخت قسط')
@section('header_subtitle', 'پرداخت قسط عضو دیگر')

@section('content')

    <div class="customer-other-installment">

        {{-- =========================================================
             Header
        ========================================================== --}}
        <div class="customer-other-installment-header">

            <div class="customer-other-installment-header-icon">
                <i class="bi bi-people-fill"></i>
            </div>

            <div class="customer-other-installment-header-content">

                <h2>
                    پرداخت اقساط دیگران
                </h2>

                <p>
                    شماره وام عضو موردنظر را وارد کنید.
                </p>

            </div>

        </div>

        @if (isset($searchError))


            <div class="customer-other-installment-search-error-box">

                <div class="customer-other-installment-search-error-icon">
                    <i class="bi bi-search"></i>
                </div>

                <div class="customer-other-installment-search-error-content">

        <span class="customer-other-installment-search-error-label">
            نتیجه جستجو
        </span>

                    <strong>
                        {{ $searchError }}
                    </strong>

                </div>

            </div>


        @endif


        {{-- =========================================================
             Search
             فقط زمانی نمایش داده می‌شود که وام پیدا نشده باشد
        ========================================================== --}}
        @if (!$loan)

            <div class="customer-other-installment-card">

                <div class="customer-other-installment-card-title">

                <span class="customer-other-installment-card-title-icon">
                    <i class="bi bi-search"></i>
                </span>

                    <span>
                    جستجوی قسط
                </span>

                </div>


                <form
                    method="GET"
                    action="{{ route('customer.installments.others.create') }}">

                    <div class="customer-other-installment-search-row">

                        <div class="customer-other-installment-search">

                            <label
                                for="loan_number"
                                class="customer-other-installment-search-label">

                                شماره وام

                            </label>


                            <div class="customer-other-installment-search-input">

                            <span class="customer-other-installment-search-icon">
                                <i class="bi bi-credit-card-2-front"></i>
                            </span>

                                <input
                                    id="loan_number"
                                    type="text"
                                    name="loan_number"
                                    value="{{ request('loan_number') }}"
                                    placeholder="شماره وام را وارد کنید"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    required>

                            </div>


                            @error('loan_number')

                            <div class="customer-other-installment-search-error">

                                <i class="bi bi-exclamation-circle"></i>

                                {{ $message }}

                            </div>

                            @enderror

                        </div>


                        <button
                            type="submit"
                            class="customer-other-installment-search-button">

                            <i class="bi bi-search"></i>

                            <span>
                            جستجوی قسط
                        </span>

                        </button>

                    </div>

                </form>

            </div>

        @endif


        {{-- =========================================================
             Result
             بعد از پیدا شدن وام، کارت جستجو حذف می‌شود
        ========================================================== --}}
        @if ($loan && $installment)

            <div class="customer-other-installment-card">

                {{-- =================================================
                     عنوان
                ================================================== --}}
                <div class="customer-other-installment-card-title">

                <span class="customer-other-installment-card-title-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </span>

                    <span>
                    اطلاعات قسط
                </span>

                </div>


                {{-- =================================================
                     اطلاعات قسط
                ================================================== --}}
                <div class="customer-other-installment-info-grid">

                    {{-- نام عضو --}}
                    <div class="customer-other-installment-info-item">

                    <span class="customer-other-installment-info-label">
                        نام عضو
                    </span>

                        <strong>
                            {{ $loan->customer->full_name }}
                        </strong>

                    </div>


                    {{-- شماره وام --}}
                    <div class="customer-other-installment-info-item">

                    <span class="customer-other-installment-info-label">
                        شماره وام
                    </span>

                        <strong dir="ltr">
                            {{ $loan->full_loan_number }}
                        </strong>

                    </div>


                    {{-- نوع وام --}}
                    <div class="customer-other-installment-info-item">

                    <span class="customer-other-installment-info-label">
                        نوع وام
                    </span>

                        <strong>
                            {{ $loan->loanType->name }}
                        </strong>

                    </div>


                    {{-- شماره قسط --}}
                    <div class="customer-other-installment-info-item">

                    <span class="customer-other-installment-info-label">
                        شماره قسط
                    </span>

                        <strong>
                            {{ $installment->installment_number }}
                        </strong>

                    </div>

                </div>


                {{-- =================================================
                     Notice
                ================================================== --}}
                <div class="customer-other-installment-notice">

                    <div class="customer-other-installment-notice-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>

                    <div class="customer-other-installment-notice-content">

                        <strong>
                            توجه
                        </strong>

                        <p>
                            این قسط متعلق به عضو دیگری است.
                            اطلاعات را بررسی کرده و در صورت تأیید پرداخت کنید.
                        </p>

                    </div>

                </div>


                {{-- =================================================
                     Payment
                ================================================== --}}
                <form
                    method="POST"
                    action="{{ route('customer.installments.others.pay') }}">

                    @csrf

                    <input
                        type="hidden"
                        name="installment_id"
                        value="{{ $installment->id }}">


                    <button
                        type="submit"
                        class="customer-other-installment-pay-button">

                    <span class="customer-other-installment-pay-icon">

                        <i class="bi bi-credit-card-fill"></i>

                    </span>


                        <span class="customer-other-installment-pay-content">

                        <strong>
                            پرداخت قسط
                        </strong>

                        <span class="customer-other-installment-pay-amount">

                            {{ number_format($installment->amount) }}

                            <small>
                                ریال
                            </small>

                        </span>

                    </span>


                        <i class="bi bi-arrow-left customer-other-installment-pay-arrow"></i>

                    </button>

                </form>

            </div>

        @endif

    </div>


@endsection

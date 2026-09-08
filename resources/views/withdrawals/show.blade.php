@extends('layouts.app')

@section('title', 'مشاهده درخواست برداشت')



@section('content')

    <div class="container-fluid withdrawal-show-page">

        {{-- =========================================================
             HEADER
        ========================================================== --}}

        <div class="withdrawal-show-header">

            <div class="withdrawal-show-title-wrapper">

                <div class="withdrawal-show-title-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <div>

                    <h5 class="withdrawal-show-title">
                        جزئیات درخواست برداشت
                    </h5>

                    <p class="withdrawal-show-subtitle">
                        مشاهده و مدیریت درخواست برداشت مشتری
                    </p>

                </div>

            </div>

            <a
                href="{{ route('withdrawals.index') }}"
                class="withdrawal-back-btn"
            >
                <i class="bi bi-arrow-right"></i>
                <span>بازگشت به درخواست‌ها</span>
            </a>

        </div>


        {{-- =========================================================
             ALERTS
        ========================================================== --}}

        @if(session('success'))

            <div class="withdrawal-alert withdrawal-alert-success">

                <span class="withdrawal-alert-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </span>

                <span>
                    {{ session('success') }}
                </span>

            </div>

        @endif


        @if(session('error'))

            <div class="withdrawal-alert withdrawal-alert-danger">

                <span class="withdrawal-alert-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </span>

                <span>
                    {{ session('error') }}
                </span>

            </div>

        @endif


        @if($errors->any())

            <div class="withdrawal-alert withdrawal-alert-danger">

                <span class="withdrawal-alert-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </span>

                <div>

                    <strong>
                        لطفاً خطاهای زیر را بررسی کنید:
                    </strong>

                    <ul class="withdrawal-error-list">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            </div>

        @endif


        {{-- =========================================================
             WITHDRAWAL INFORMATION
        ========================================================== --}}

        <div class="card withdrawal-info-card">

            <div class="withdrawal-card-header">

                <div class="withdrawal-card-title">

                    <span class="withdrawal-card-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </span>

                    <div>

                        <h6>
                            اطلاعات درخواست
                        </h6>

                        <small>
                            مشخصات اصلی درخواست برداشت
                        </small>

                    </div>

                </div>


                @if($withdrawal->status instanceof \App\Enums\WithdrawalStatus)

                    <span class="withdrawal-status-badge">

                        <span class="withdrawal-status-dot"></span>

                        {{ $withdrawal->status->label() }}

                    </span>

                @endif

            </div>


            <div class="card-body withdrawal-info-body">

                <div class="withdrawal-info-grid">

                    {{-- مشتری --}}
                    <div class="withdrawal-info-item">

                        <span class="withdrawal-info-label">
                            <i class="bi bi-person"></i>
                            مشتری
                        </span>

                        <strong>
                            {{ $withdrawal->account->customer->first_name }}
                            {{ $withdrawal->account->customer->last_name }}
                        </strong>

                    </div>


                    {{-- شماره حساب --}}
                    <div class="withdrawal-info-item">

                        <span class="withdrawal-info-label">
                            <i class="bi bi-credit-card"></i>
                            شماره حساب
                        </span>

                        <strong
                            dir="ltr"
                            class="withdrawal-ltr-value"
                        >
                            {{ $withdrawal->account->account_number }}
                        </strong>

                    </div>


                    {{-- مبلغ --}}
                    <div class="withdrawal-info-item withdrawal-amount-item">

                        <span class="withdrawal-info-label">
                            <i class="bi bi-cash-stack"></i>
                            مبلغ برداشت
                        </span>

                        <div class="withdrawal-amount-value">

                            <strong>
                                {{ number_format($withdrawal->amount) }}
                            </strong>

                            <span>
                                ریال
                            </span>

                        </div>

                    </div>


                    {{-- وضعیت --}}
                    <div class="withdrawal-info-item">

                        <span class="withdrawal-info-label">
                            <i class="bi bi-info-circle"></i>
                            وضعیت
                        </span>

                        @if($withdrawal->status instanceof \App\Enums\WithdrawalStatus)

                            <span class="withdrawal-status-badge">

                                <span class="withdrawal-status-dot"></span>

                                {{ $withdrawal->status->label() }}

                            </span>

                        @else

                            <span class="withdrawal-status-badge withdrawal-status-unknown">
                                نامشخص
                            </span>

                        @endif

                    </div>


                    {{-- شبا --}}
                    <div class="withdrawal-info-item withdrawal-info-item-wide">

                        <span class="withdrawal-info-label">
                            <i class="bi bi-bank"></i>
                            شماره شبا
                        </span>

                        <div class="withdrawal-copy-value" dir="ltr">

                            {{ \App\Support\Iban::format($withdrawal->iban) }}

                        </div>

                    </div>


                    {{-- بانک مقصد --}}
                    <div class="withdrawal-info-item">

                        <span class="withdrawal-info-label">
                            <i class="bi bi-building"></i>
                            بانک مقصد
                        </span>

                        <strong>
                            {{ \App\Support\Iban::bankName($withdrawal->iban) }}
                        </strong>

                    </div>


                    {{-- تاریخ درخواست --}}
                    <div class="withdrawal-info-item">

                        <span class="withdrawal-info-label">
                            <i class="bi bi-calendar3"></i>
                            تاریخ درخواست
                        </span>

                        <strong dir="ltr">

                            {{ \Morilog\Jalali\Jalalian::fromDateTime(
                                $withdrawal->created_at
                            )->format('Y/m/d H:i') }}

                        </strong>

                    </div>


                    {{-- توضیحات --}}
                    <div class="withdrawal-info-item">

                        <span class="withdrawal-info-label">
                            <i class="bi bi-chat-left-text"></i>
                            توضیحات
                        </span>

                        <strong>
                            {{ $withdrawal->description ?? '-' }}
                        </strong>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             PAID INFORMATION
        ========================================================== --}}

        @if($withdrawal->status === \App\Enums\WithdrawalStatus::PAID)

            <div class="card withdrawal-paid-card">

                <div class="withdrawal-paid-header">

                    <div class="withdrawal-card-title">

                        <span class="withdrawal-paid-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </span>

                        <div>

                            <h6>
                                اطلاعات پرداخت
                            </h6>

                            <small>
                                جزئیات پرداخت انجام‌شده
                            </small>

                        </div>

                    </div>

                    <span class="withdrawal-paid-label">
                        پرداخت شده
                    </span>

                </div>


                <div class="card-body withdrawal-info-body">

                    <div class="withdrawal-info-grid">

                        {{-- بانک پرداخت --}}
                        <div class="withdrawal-info-item">

                            <span class="withdrawal-info-label">
                                <i class="bi bi-bank"></i>
                                بانک پرداخت‌کننده
                            </span>

                            <strong>

                                @if($withdrawal->payment_bank instanceof \App\Enums\PaymentBank)

                                    {{ $withdrawal->payment_bank->label() }}

                                @else

                                    -

                                @endif

                            </strong>

                        </div>


                        {{-- کد پیگیری --}}
                        <div class="withdrawal-info-item">

                            <span class="withdrawal-info-label">
                                <i class="bi bi-upc-scan"></i>
                                کد پیگیری پرداخت
                            </span>

                            <strong dir="ltr">
                                {{ $withdrawal->payment_tracking_code ?? '-' }}
                            </strong>

                        </div>


                        {{-- ثبت‌کننده --}}
                        <div class="withdrawal-info-item">

                            <span class="withdrawal-info-label">
                                <i class="bi bi-person-check"></i>
                                ثبت‌کننده پرداخت
                            </span>

                            <strong>
                                {{ $withdrawal->paidBy?->username ?? '-' }}
                            </strong>

                        </div>


                        {{-- تاریخ پرداخت --}}
                        <div class="withdrawal-info-item">

                            <span class="withdrawal-info-label">
                                <i class="bi bi-calendar-check"></i>
                                تاریخ پرداخت
                            </span>

                            <strong dir="ltr">

                                @if($withdrawal->paid_at)

                                    {{ \Morilog\Jalali\Jalalian::fromDateTime(
                                        $withdrawal->paid_at
                                    )->format('Y/m/d H:i') }}

                                @else

                                    -

                                @endif

                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        @endif


        {{-- =========================================================
             PENDING ACTIONS
        ========================================================== --}}

        @if($withdrawal->status === \App\Enums\WithdrawalStatus::PENDING)

            <div class="card withdrawal-actions-card">

                <div class="withdrawal-actions-header">

                    <div class="withdrawal-card-title">

                        <span class="withdrawal-actions-icon">
                            <i class="bi bi-gear"></i>
                        </span>

                        <div>

                            <h6>
                                مدیریت درخواست
                            </h6>

                            <small>
                                ثبت پرداخت یا رد درخواست برداشت
                            </small>

                        </div>

                    </div>

                </div>


                <div class="card-body withdrawal-actions-body">

                    {{-- ثبت پرداخت --}}
                    <div class="withdrawal-action-section">

                        <div class="withdrawal-action-heading">

                            <span class="withdrawal-action-heading-icon">
                                <i class="bi bi-check-circle"></i>
                            </span>

                            <div>

                                <strong>
                                    ثبت پرداخت
                                </strong>

                                <small>
                                    پس از انجام انتقال وجه، اطلاعات پرداخت را ثبت کنید.
                                </small>

                            </div>

                        </div>


                        <form
                            method="POST"
                            action="{{ route('withdrawals.approve', $withdrawal) }}"
                        >

                            @csrf

                            <div class="withdrawal-form-grid">

                                {{-- بانک پرداخت‌کننده --}}
                                <div class="withdrawal-form-group">

                                    <label class="withdrawal-form-label">
                                        بانک پرداخت‌کننده
                                        <span>*</span>
                                    </label>

                                    <select
                                        name="payment_bank"
                                        class="form-select withdrawal-form-control @error('payment_bank') is-invalid @enderror"
                                        required
                                    >

                                        <option value="">
                                            انتخاب بانک
                                        </option>

                                        @foreach(\App\Enums\PaymentBank::cases() as $bank)

                                            <option
                                                value="{{ $bank->value }}"
                                                @selected(old('payment_bank') == $bank->value)
                                            >
                                            {{ $bank->label() }}
                                            </option>

                                        @endforeach

                                    </select>

                                    @error('payment_bank')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                    @enderror

                                </div>


                                {{-- کد پیگیری --}}
                                <div class="withdrawal-form-group">

                                    <label class="withdrawal-form-label">
                                        کد پیگیری پرداخت
                                        <span>*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="payment_tracking_code"
                                        class="form-control withdrawal-form-control @error('payment_tracking_code') is-invalid @enderror"
                                        value="{{ old('payment_tracking_code') }}"
                                        maxlength="100"
                                        autocomplete="off"
                                        required
                                    >

                                    @error('payment_tracking_code')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                    @enderror

                                </div>

                            </div>


                            <div class="withdrawal-form-actions">

                                <button
                                    type="submit"
                                    class="withdrawal-action-btn withdrawal-action-btn-success"
                                    onclick="return confirm('آیا پرداخت این درخواست انجام شده است؟')"
                                >

                                    <i class="bi bi-check-circle-fill"></i>

                                    <span>
                                        ثبت پرداخت
                                    </span>

                                </button>

                            </div>

                        </form>

                    </div>


                    {{-- رد درخواست --}}
                    <div class="withdrawal-reject-section">

                        <div class="withdrawal-action-heading">

                            <span class="withdrawal-action-heading-icon withdrawal-reject-icon">
                                <i class="bi bi-x-circle"></i>
                            </span>

                            <div>

                                <strong>
                                    رد درخواست
                                </strong>

                                <small>
                                    در صورت رد، مبلغ به حساب مشتری بازگردانده خواهد شد.
                                </small>

                            </div>

                        </div>


                        <form
                            method="POST"
                            action="{{ route('withdrawals.reject', $withdrawal) }}"
                        >

                            @csrf

                            <button
                                type="submit"
                                class="withdrawal-action-btn withdrawal-action-btn-danger"
                                onclick="return confirm('آیا از رد این درخواست اطمینان دارید؟ مبلغ به حساب مشتری بازگردانده خواهد شد.')"
                            >

                                <i class="bi bi-x-circle-fill"></i>

                                <span>
                                    رد درخواست
                                </span>

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        @endif

    </div>

@endsection

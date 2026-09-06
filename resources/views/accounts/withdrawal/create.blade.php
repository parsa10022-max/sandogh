@extends('layouts.app')

@section('title', 'درخواست برداشت')

@section('content')

    <div class="container-fluid withdrawal-page">

        {{-- سربرگ --}}
        <div class="withdrawal-header">

            <div class="withdrawal-title-wrapper">

                <div class="withdrawal-title-icon">
                    <i class="bi bi-arrow-down-circle"></i>
                </div>

                <div>
                    <h5 class="withdrawal-title">
                        درخواست برداشت
                    </h5>

                    <p class="withdrawal-subtitle">
                        ثبت درخواست برداشت از حساب پس‌انداز
                    </p>
                </div>

            </div>

            <a
                href="{{ route('accounts.show', $account) }}"
                class="withdrawal-back-btn"
            >
                <i class="bi bi-arrow-right"></i>
                <span>بازگشت به حساب</span>
            </a>

        </div>


        {{-- پیام موفقیت --}}
        @if(session('success'))

            <div class="withdrawal-alert success">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>

        @endif


        {{-- خطاها --}}
        @if($errors->any())

            <div class="withdrawal-alert danger">

                <div class="withdrawal-alert-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>

                <div>

                    <strong>
                        لطفاً موارد زیر را بررسی کنید:
                    </strong>

                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>

            </div>

        @endif


        {{-- اطلاعات حساب --}}
        <div class="card withdrawal-account-card">

            <div class="withdrawal-section-header">

                <div class="withdrawal-section-title">

                <span class="withdrawal-section-icon">
                    <i class="bi bi-wallet2"></i>
                </span>

                    <div>
                        <h6>اطلاعات حساب</h6>
                        <small>اطلاعات صاحب حساب و حساب مقصد</small>
                    </div>

                </div>

            </div>


            <div class="card-body withdrawal-account-body">

                <div class="withdrawal-account-grid">

                    {{-- عضو --}}
                    <div class="withdrawal-account-item">

                    <span class="withdrawal-info-label">
                        <i class="bi bi-person"></i>
                        عضو
                    </span>

                        <strong>
                            {{ $account->customer->first_name }}
                            {{ $account->customer->last_name }}
                        </strong>

                    </div>


                    {{-- شماره حساب --}}
                    <div class="withdrawal-account-item">

                    <span class="withdrawal-info-label">
                        <i class="bi bi-credit-card"></i>
                        شماره حساب
                    </span>

                        <strong dir="ltr">
                            {{ $account->account_number }}
                        </strong>

                    </div>


                    {{-- بانک مقصد --}}
                    <div class="withdrawal-account-item">

                    <span class="withdrawal-info-label">
                        <i class="bi bi-bank"></i>
                        بانک مقصد
                    </span>

                        <strong>
                            {{ \App\Support\Iban::bankName($account->customer->iban) }}
                        </strong>

                    </div>

                </div>

            </div>

        </div>


        {{-- فرم برداشت --}}
        <div class="card withdrawal-form-card">

            <div class="withdrawal-section-header">

                <div class="withdrawal-section-title">

                <span class="withdrawal-section-icon withdrawal-form-icon">
                    <i class="bi bi-cash-stack"></i>
                </span>

                    <div>
                        <h6>اطلاعات برداشت</h6>
                        <small>مبلغ و حساب مقصد را وارد کنید</small>
                    </div>

                </div>

            </div>


            <div class="card-body withdrawal-form-body">

                <form
                    method="POST"
                    action="{{ route('accounts.withdrawal.store', $account) }}"
                >

                    @csrf


                    {{-- موجودی قابل برداشت --}}
                    <div class="withdrawal-balance-box">

                        <div class="withdrawal-balance-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>

                        <div class="withdrawal-balance-content">

                        <span>
                            موجودی قابل برداشت
                        </span>

                            <strong>
                                {{ number_format($account->balance) }}
                                <small>ریال</small>
                            </strong>

                        </div>

                    </div>


                    <input
                        type="hidden"
                        id="availableBalance"
                        value="{{ $account->balance }}"
                    >


                    {{-- شماره شبا --}}
                    <div class="withdrawal-field">

                        <x-inputs.iban-input
                            name="iban"
                            label="شماره شبا مقصد"
                            :value="\App\Support\Iban::formatDigits($account->customer->iban)"
                            live
                            required
                        />

                        <div class="withdrawal-info-alert">

                            <i class="bi bi-info-circle"></i>

                            <span>
                            شماره شبای فعلی شما نمایش داده شده است.
                            در صورت تغییر، شماره شبای جدید را وارد کنید.
                        </span>

                        </div>

                    </div>


                    {{-- مبلغ برداشت --}}
                    <div class="withdrawal-field">

                        <label
                            for="amount"
                            class="withdrawal-form-label"
                        >
                            مبلغ برداشت
                        </label>

                        <div class="withdrawal-input-wrapper">

                            <input
                                type="text"
                                id="amount"
                                name="amount"
                                class="form-control withdrawal-amount-input money-input @error('amount') is-invalid @enderror"
                                value="{{ old('amount') }}"
                                data-min="500000"
                                data-max="{{ $account->balance }}"
                                data-live="true"
                                inputmode="numeric"
                                autocomplete="off"
                                required
                            >

                            <span class="withdrawal-input-unit">
            ریال
        </span>

                        </div>

                        <div
                            id="amountError"
                            class="withdrawal-amount-error"
                        ></div>

                        @error('amount')
                        <div class="withdrawal-validation-error">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                        @enderror

                        <small class="withdrawal-field-hint">
                            حداقل مبلغ برداشت ۵۰۰٬۰۰۰ ریال است.
                        </small>

                    </div>


                    {{-- توضیحات --}}
                    <div class="withdrawal-field">

                        <label
                            for="description"
                            class="withdrawal-form-label"
                        >
                            توضیحات
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            class="form-control withdrawal-description"
                            rows="3"
                            placeholder="در صورت نیاز توضیحات درخواست را وارد کنید..."
                        >{{ old('description') }}</textarea>

                    </div>


                    {{-- عملیات --}}
                    <div class="withdrawal-actions">

                        <a
                            href="{{ route('accounts.show', $account) }}"
                            class="withdrawal-cancel-btn"
                        >
                            <i class="bi bi-x-lg"></i>
                            <span>انصراف</span>
                        </a>

                        <button
                            id="submitBtn"
                            type="submit"
                            class="withdrawal-submit-btn"
                        >
                            <i class="bi bi-check2-circle"></i>
                            <span>ثبت درخواست برداشت</span>
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>




@endsection

@extends('layouts.app')

@section('title', 'واریز به حساب پس‌انداز')



@section('content')


    <div class="container py-4 account-deposit-page">

        {{-- Header --}}
        <div class="account-deposit-header">

            <div class="account-deposit-title-wrapper">

                <div class="account-deposit-title-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <div>
                    <h4 class="account-deposit-title">
                        واریز به حساب پس‌انداز
                    </h4>

                    <div class="account-deposit-subtitle">
                        ثبت واریز جدید به حساب
                    </div>
                </div>

            </div>

            <a href="{{ route('accounts.show', $account) }}"
               class="account-deposit-back-btn">

                <i class="bi bi-arrow-right"></i>

                بازگشت

            </a>

        </div>


        {{-- پیام موفقیت --}}
        @if(session('success'))
            <div class="account-deposit-alert account-deposit-alert--success">

                <i class="bi bi-check-circle-fill"></i>

                <span>
                {{ session('success') }}
            </span>

            </div>
        @endif


        {{-- خطاهای عمومی --}}
        @if($errors->any())
            <div class="account-deposit-alert account-deposit-alert--danger">

                <i class="bi bi-exclamation-triangle-fill"></i>

                <div>

                    <strong>
                        اطلاعات وارد شده صحیح نیست.
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
        <div class="account-deposit-card account-deposit-account-card">

            <div class="account-deposit-card-header">

                <div class="account-deposit-section-icon">
                    <i class="bi bi-person-vcard"></i>
                </div>

                <div>
                    <div class="account-deposit-card-title">
                        اطلاعات حساب
                    </div>

                    <div class="account-deposit-card-subtitle">
                        حساب مقصد واریز
                    </div>
                </div>

            </div>


            <div class="account-deposit-card-body">

                <div class="account-deposit-account-grid">

                    {{-- مالک حساب --}}
                    <div class="account-deposit-account-item">

                    <span class="account-deposit-label">
                        مالک حساب
                    </span>

                        <strong>
                            @if($account->customer)
                                {{ $account->customer->first_name }}
                                {{ $account->customer->last_name }}
                            @else
                                حساب سیستمی
                            @endif
                        </strong>

                    </div>


                    {{-- شماره حساب --}}
                    <div class="account-deposit-account-item">

                    <span class="account-deposit-label">
                        شماره حساب
                    </span>

                        <strong dir="ltr">
                            {{ $account->account_number }}
                        </strong>

                    </div>


                    {{-- موجودی --}}
                    <div class="account-deposit-account-item account-deposit-balance-item">

                    <span class="account-deposit-label">
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

            </div>

        </div>


        {{-- فرم واریز --}}
        <div class="account-deposit-card">

            <div class="account-deposit-card-header">

                <div class="account-deposit-section-icon">
                    <i class="bi bi-arrow-down-circle"></i>
                </div>

                <div>
                    <div class="account-deposit-card-title">
                        ثبت واریز
                    </div>

                    <div class="account-deposit-card-subtitle">
                        مبلغ و روش واریز را مشخص کنید
                    </div>
                </div>

            </div>


            <div class="account-deposit-card-body">

                <form method="POST"
                      action="{{ route('accounts.deposit') }}"
                      id="depositForm">

                    @csrf


                    {{-- حساب مقصد --}}
                    <div class="account-deposit-field">

                        <label for="account_id"
                               class="account-deposit-form-label">

                            حساب مقصد

                            <span>*</span>

                        </label>

                        <div class="account-deposit-input-wrapper">

                            <div class="account-deposit-input-icon">
                                <i class="bi bi-wallet2"></i>
                            </div>

                            <select name="account_id"
                                    id="account_id"
                                    class="form-select account-deposit-select @error('account_id') is-invalid @enderror"
                                    required>

                                <option value="{{ $account->id }}">
                                    {{ $account->name ?? 'حساب' }}
                                    -
                                    {{ $account->account_number }}
                                </option>

                            </select>

                        </div>

                        @error('account_id')
                        <div class="account-deposit-error">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>


                    {{-- مبلغ --}}
                    <div class="account-deposit-field">

                        <label for="amount"
                               class="account-deposit-form-label">

                            مبلغ واریز

                            <span>*</span>

                        </label>

                        <div class="account-deposit-amount-wrapper">

                            <input
                                type="text"
                                name="amount"
                                id="amount"
                                value="{{ old('amount') }}"
                                class="form-control money-input account-deposit-amount-input @error('amount') is-invalid @enderror"
                                inputmode="numeric"
                                autocomplete="off"
                                data-live="true"
                                data-min="50000"
                                placeholder="مثلاً ۵۰۰٬۰۰۰"
                                required
                            >

                            <span class="account-deposit-input-unit">
                            ریال
                        </span>

                        </div>

                        <div class="account-deposit-help">
                            حداقل مبلغ واریز ۵۰٬۰۰۰ ریال است.
                        </div>

                        @error('amount')
                        <div class="account-deposit-error">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>


                    {{-- روش واریز --}}
                    <div class="account-deposit-field">

                        <label class="account-deposit-form-label">
                            روش واریز
                            <span>*</span>
                        </label>

                        <div class="account-deposit-payment-grid">

                            @foreach(\App\Enums\PaymentMethod::cases() as $method)

                                @php
                                    $paymentIcon = match ($method) {
                                        \App\Enums\PaymentMethod::CASH =>
                                            'bi-cash-stack',

                                        \App\Enums\PaymentMethod::POS =>
                                            'bi-credit-card-2-front',

                                        \App\Enums\PaymentMethod::GATEWAY =>
                                            'bi-globe2',

                                        \App\Enums\PaymentMethod::LOAN_DISBURSEMENT =>
                                            'bi-bank',

                                        \App\Enums\PaymentMethod::BANK_TRANSFER =>
                                            'bi-arrow-left-right',

                                        default =>
                                            'bi-credit-card',
                                    };
                                @endphp

                                <label class="account-deposit-payment-option">

                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="{{ $method->value }}"
                                        class="account-deposit-payment-radio"
                                        @checked(old('payment_method') == $method->value)
                                    required
                                    >

                                    <span class="account-deposit-payment-content">

                    <span class="account-deposit-payment-icon">
                        <i class="bi {{ $paymentIcon }}"></i>
                    </span>

                    <span class="account-deposit-payment-text">
                        {{ $method->label() }}
                    </span>

                    <span class="account-deposit-payment-check">
                        <i class="bi bi-check-circle-fill"></i>
                    </span>

                </span>

                                </label>

                            @endforeach

                        </div>

                        @error('payment_method')
                        <div class="account-deposit-error">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>





                    {{-- توضیحات --}}
                    <div class="account-deposit-field">

                        <label for="description"
                               class="account-deposit-form-label">

                            توضیحات

                        </label>

                        <textarea
                            name="description"
                            id="description"
                            rows="4"
                            maxlength="255"
                            class="form-control account-deposit-description @error('description') is-invalid @enderror"
                            placeholder="توضیحات مربوط به این واریز را وارد کنید..."
                        >{{ old('description') }}</textarea>

                        @error('description')
                        <div class="account-deposit-error">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>


                    {{-- هشدار --}}
                    <div class="account-deposit-info">

                        <i class="bi bi-info-circle-fill"></i>

                        <span>
                        پس از ثبت، مبلغ به موجودی حساب اضافه شده و
                        تراکنش در گردش حساب ثبت می‌شود.
                    </span>

                    </div>


                    {{-- دکمه‌ها --}}
                    <div class="account-deposit-actions">

                        <a href="{{ route('accounts.show', $account) }}"
                           class="account-deposit-cancel-btn">

                            <i class="bi bi-x-circle"></i>

                            انصراف

                        </a>

                        <button type="submit"
                                class="account-deposit-submit-btn">

                            <i class="bi bi-check-circle"></i>

                            ثبت واریز

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection

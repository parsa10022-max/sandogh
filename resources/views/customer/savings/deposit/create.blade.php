@extends('customer.layouts.app')

@section('title', 'واریز به حساب پس‌انداز')

@push('styles')
    @vite('resources/css/customer/savings-deposit.css')
@endpush

@section('content')

    <div class="customer-savings-deposit-page">

        <div class="customer-savings-deposit-card">

            {{-- =====================================================
                 Header
                 ===================================================== --}}

            <div class="customer-savings-deposit-header">

                <div class="customer-savings-deposit-header-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <div class="customer-savings-deposit-header-content">

                    <h1>
                        واریز به حساب پس‌انداز
                    </h1>

                    <span>
                        افزایش موجودی حساب پس‌انداز
                    </span>

                </div>

            </div>


            {{-- =====================================================
                 Account Information
                 ===================================================== --}}

            <div class="customer-savings-deposit-account">

                <div class="customer-savings-deposit-account-item">

                    <span class="customer-savings-deposit-account-label">
                        صاحب حساب
                    </span>

                    <strong>
                        {{ $account->customer->first_name }}
                        {{ $account->customer->last_name }}
                    </strong>

                </div>


                <div class="customer-savings-deposit-account-item">

                    <span class="customer-savings-deposit-account-label">
                        شماره حساب
                    </span>

                    <strong
                        dir="ltr"
                        class="customer-savings-deposit-account-number"
                    >
                        {{ $account->prefix }}
                        {{ $account->account_number }}
                    </strong>

                </div>


                <div class="customer-savings-deposit-account-item customer-savings-deposit-account-balance">

                    <span class="customer-savings-deposit-account-label">
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
                 Form
                 ===================================================== --}}

            <form
                method="POST"
                action="{{ route('customer.savings.deposit.store') }}"
                class="customer-savings-deposit-form"
            >

                @csrf


                {{-- مبلغ --}}

                <div class="customer-savings-deposit-field">

                    <label
                        for="amount"
                        class="customer-savings-deposit-label"
                    >

                        <i class="bi bi-cash-stack"></i>

                        مبلغ واریز

                    </label>


                    <div class="customer-savings-deposit-money">

                        <input
                            type="text"
                            name="amount"
                            id="amount"
                            class="customer-savings-deposit-money-input money-input @error('amount') is-invalid @enderror"
                            value="{{ old('amount') }}"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="مثلاً 500,000"
                            data-min="50000"
                            required
                        >

                        <span class="customer-savings-deposit-rial">
                            ریال
                        </span>

                    </div>


                    @error('amount')

                    <div class="customer-savings-deposit-error">
                        {{ $message }}
                    </div>

                    @enderror


                    <div class="customer-savings-deposit-help">

                        <i class="bi bi-info-circle"></i>

                        <span>
                            حداقل مبلغ واریز
                        </span>

                        <strong>
                            ۵۰,۰۰۰ ریال
                        </strong>

                        <span>
                            است.
                        </span>

                    </div>

                </div>


                {{-- دکمه --}}

                <div class="customer-savings-deposit-actions">

                    <button
                        type="submit"
                        class="customer-savings-deposit-submit"
                    >

                        <i class="bi bi-credit-card"></i>

                        <span>
                            ادامه پرداخت
                        </span>

                        <i class="bi bi-arrow-left"></i>

                    </button>

                </div>

            </form>


            {{-- =====================================================
                 Security Note
                 ===================================================== --}}

            <div class="customer-savings-deposit-note">

                <i class="bi bi-shield-check"></i>

                <span>
                    پرداخت شما از طریق درگاه امن انجام می‌شود.
                </span>

            </div>

        </div>

    </div>

@endsection

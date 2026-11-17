@extends('layouts.app')

@section('title', 'گردش حساب')

@section('content')


    <div class="container-fluid account-transactions-page">

        {{-- سربرگ صفحه --}}
        <div class="account-transactions-header">

            <div class="account-transactions-title-wrapper">

                <div class="account-transactions-title-icon">
                    <i class="bi bi-arrow-left-right"></i>
                </div>

                <div>
                    <h5 class="account-transactions-title">
                        گردش حساب
                    </h5>

                    <p class="account-transactions-subtitle">
                        مشاهده سوابق تراکنش‌های حساب
                    </p>
                </div>

            </div>

            <a
                href="{{ route('accounts.show', $account) }}"
                class="account-transactions-back-btn"
            >
                <i class="bi bi-arrow-right"></i>
                <span>بازگشت به حساب</span>
            </a>

        </div>


        {{-- خلاصه حساب --}}
        <div class="card account-summary-card">

            <div class="account-summary-header">

                <div class="account-summary-title">

                    <span class="account-summary-icon">
                        <i class="bi bi-wallet2"></i>
                    </span>

                    <span>خلاصه حساب</span>

                </div>

                <span class="account-number-badge" dir="ltr">
                    {{ $account->account_number }}
                </span>

            </div>


            <div class="card-body account-summary-body">

                <div class="account-summary-grid">

                    {{-- عنوان حساب --}}
                    <div class="account-summary-item">

                        <span class="account-summary-label">
                            <i class="bi bi-card-heading"></i>
                            عنوان حساب
                        </span>

                        <strong>
                            {{ $account->name ?? 'حساب مشتری' }}
                        </strong>

                    </div>


                    {{-- مالک حساب --}}
                    <div class="account-summary-item">

                        <span class="account-summary-label">
                            <i class="bi bi-person"></i>
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


                    {{-- موجودی --}}
                    <div class="account-summary-item account-current-balance">

                        <span class="account-summary-label">
                            <i class="bi bi-cash-stack"></i>
                            موجودی فعلی
                        </span>

                        <div class="account-balance-value">

                            <strong>
                                {{ fa_money($account->balance) }}
                            </strong>

                            <span>ریال</span>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- جدول تراکنش‌ها --}}
        <div class="card account-transactions-card">

            <div class="account-transactions-card-header">

                <div class="account-transactions-card-title">

                    <span class="account-transactions-card-icon">
                        <i class="bi bi-list-ul"></i>
                    </span>

                    <div>

                        <h6>
                            تراکنش‌ها
                        </h6>

                        <small>
                            فهرست کامل گردش حساب
                        </small>

                    </div>

                </div>

            </div>


            <div class="card-body account-transactions-body">

                <div class="table-responsive account-transactions-table-wrapper">

                    <table class="table align-middle account-transactions-table">

                        <thead>

                        <tr>

                            <th>تاریخ</th>

                            <th>شماره تراکنش</th>

                            <th>روش پرداخت</th>

                            <th>نوع تراکنش</th>

                            <th>مبلغ</th>

                            <th>مانده</th>

                            <th>توضیح</th>

                        </tr>

                        </thead>


                        <tbody>

                        @forelse($transactions as $transaction)

                            <tr>

                                {{-- تاریخ --}}
                                <td>

                                    <span class="transaction-date">
                                        {{ app(\App\Services\Date\JalaliDateService::class)
                                            ->toJalali($transaction->transaction_date) }}
                                    </span>

                                </td>


                                {{-- شماره تراکنش --}}
                                <td>

                                    <span class="transaction-number" dir="ltr">
                                        {{ $transaction->transaction_no }}
                                    </span>

                                </td>


                                {{-- روش پرداخت --}}
                                <td>

                                    <span class="transaction-payment-method">
                                        {{ $transaction->payment_method?->label() ?? '-' }}
                                    </span>

                                </td>



                                {{-- نوع تراکنش --}}
                                <td>
                                    @php
                                        $type = $transaction->transaction_type;
                                        $method = $transaction->payment_method;

                                        $transactionLabel = match ($type) {
                                            \App\Enums\TransactionType::DEPOSIT => match ($method) {
                                                \App\Enums\PaymentMethod::CASH => 'واریزی نقدی',
                                                \App\Enums\PaymentMethod::POS => 'واریزی با پوز',
                                                \App\Enums\PaymentMethod::GATEWAY => 'واریزی آنلاین',
                                                \App\Enums\PaymentMethod::BANK_TRANSFER => 'واریزی بانکی',
                                                \App\Enums\PaymentMethod::LOAN_DISBURSEMENT => 'واریز وام',
                                                default => 'واریز',
                                            },

                                            \App\Enums\TransactionType::WITHDRAWAL => match ($method) {
                                                \App\Enums\PaymentMethod::CASH => 'برداشت نقدی',
                                                \App\Enums\PaymentMethod::BANK_TRANSFER => 'برداشت بانکی',
                                                default => 'برداشت',
                                            },

                                            \App\Enums\TransactionType::INSTALLMENT_PAYMENT => 'پرداخت قسط وام',
                                            \App\Enums\TransactionType::TRANSFER => 'انتقال وجه',
                                            \App\Enums\TransactionType::ADJUSTMENT => 'اصلاح حساب',
                                            default => $type?->label() ?? '-',
                                        };

                                        $isDeposit = $type === \App\Enums\TransactionType::DEPOSIT;
                                        $isWithdrawal = $type === \App\Enums\TransactionType::WITHDRAWAL;
                                    @endphp

                                    <span class="transaction-type {{ $isDeposit ? 'deposit' : ($isWithdrawal ? 'withdrawal' : 'other') }}">
        <span class="transaction-type-dot"></span>

        <i class="bi {{ $isDeposit ? 'bi-arrow-down-circle' : ($isWithdrawal ? 'bi-arrow-up-circle' : 'bi-arrow-left-right') }}"></i>

        {{ $transactionLabel }}
    </span>
                                </td>



                                {{-- مبلغ --}}
                                <td>

                                    <div class="transaction-amount">

                                        <strong>
                                            {{ fa_money($transaction->amount) }}
                                        </strong>

                                        <span>ریال</span>

                                    </div>

                                </td>


                                {{-- مانده --}}
                                <td>

                                    <div class="transaction-balance">

                                        <strong>
                                            {{ fa_money($transaction->balance_after) }}
                                        </strong>

                                        <span>ریال</span>

                                    </div>

                                </td>


                                {{-- توضیح --}}
                                <td>

                                    <span class="transaction-description">
                                        {{ $transaction->description ?? '-' }}
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7">

                                    <div class="transactions-empty">

                                        <div class="transactions-empty-icon">
                                            <i class="bi bi-receipt"></i>
                                        </div>

                                        <h6>
                                            تراکنشی ثبت نشده است
                                        </h6>

                                        <p>
                                            هنوز هیچ تراکنشی برای این حساب ثبت نشده است.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- صفحه‌بندی --}}
                @if($transactions->hasPages())

                    <div class="transactions-pagination">

                        {{ $transactions->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>


@endsection



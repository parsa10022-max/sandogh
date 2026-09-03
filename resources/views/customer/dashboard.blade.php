@extends('customer.layouts.app')

@section('title', 'داشبورد مشتری')

@section('content')

    <div class="customer-dashboard">





        {{-- =========================================================
             وضعیت مالی من
        ========================================================= --}}
        <section class="customer-financial-section">

            {{-- Header --}}
            <div class="customer-financial-section-header">

                <div>
                    <h2>
                        وضعیت مالی من
                    </h2>

                    <span>
                خلاصه حساب و وام
            </span>
                </div>

                <span class="customer-financial-section-status">
            <i class="bi bi-check-circle-fill"></i>
            فعال
        </span>

            </div>


            {{-- Financial Cards --}}
            <div class="customer-financial-cards">

                {{-- مجموع موجودی --}}
                <div class="customer-financial-card-item total">

                    <div class="customer-financial-card-icon">
                        <i class="bi bi-wallet2"></i>
                    </div>

                    <div class="customer-financial-card-content">

                <span class="customer-financial-card-label">
                    مجموع موجودی
                </span>

                        <strong class="customer-financial-card-value">
                            {{ number_format($totalBalance) }}

                            <small>
                                ریال
                            </small>
                        </strong>

                    </div>

                </div>


                {{-- حساب‌های مشتری --}}
                @foreach($accounts as $account)

                    <div class="customer-financial-card-item
                @if($account->account_type === \App\Enums\AccountType::SAVING)
                        savings
@elseif($account->account_type === \App\Enums\AccountType::CURRENT)
                        current
@else
                        account
@endif
                        ">

                        <div class="customer-financial-card-icon">

                            @if($account->account_type === \App\Enums\AccountType::SAVING)

                                <i class="bi bi-piggy-bank-fill"></i>

                            @elseif($account->account_type === \App\Enums\AccountType::CURRENT)

                                <i class="bi bi-credit-card-fill"></i>

                            @else

                                <i class="bi bi-wallet2"></i>

                            @endif

                        </div>


                        <div class="customer-financial-card-content">

                    <span class="customer-financial-card-label">

                        @if($account->account_type === \App\Enums\AccountType::SAVING)

                            پس‌انداز

                        @elseif($account->account_type === \App\Enums\AccountType::CURRENT)

                            حساب جاری

                        @else

                            حساب

                        @endif

                    </span>


                            <strong class="customer-financial-card-value">
                                {{ number_format($account->balance) }}

                                <small>
                                    ریال
                                </small>
                            </strong>


                            <span class="customer-financial-card-meta">
                        حساب {{ $account->account_number }}
                    </span>

                        </div>

                    </div>

                @endforeach


                {{-- وام فعال --}}
                <div class="customer-financial-card-item loan">

                    <div class="customer-financial-card-icon">
                        <i class="bi bi-cash-coin"></i>
                    </div>

                    <div class="customer-financial-card-content">

                <span class="customer-financial-card-label">
                    وام فعال
                </span>

                        @if($activeLoan)

                            <strong class="customer-financial-card-value">

                                {{ number_format($activeLoan->remainingAmount()) }}

                                <small>
                                    ریال باقی‌مانده
                                </small>

                            </strong>

                            <span class="customer-financial-card-meta">
                        وام {{ $activeLoan->full_loan_number }}
                    </span>

                        @else

                            <strong class="customer-financial-card-value muted">
                                بدون وام فعال
                            </strong>

                        @endif

                    </div>

                </div>


                {{-- اقساط معوق --}}
                <div class="customer-financial-card-item overdue">

                    <div class="customer-financial-card-icon">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>

                    <div class="customer-financial-card-content">

                <span class="customer-financial-card-label">
                    اقساط معوق
                </span>

                        @if($overdueInstallmentsCount > 0)

                            <strong class="customer-financial-card-value danger">

                                {{ $overdueInstallmentsCount }}

                                <small>
                                    قسط
                                </small>

                            </strong>

                            <span class="customer-financial-card-meta danger-text">
                        نیاز به پرداخت
                    </span>

                        @else

                            <strong class="customer-financial-card-value success">
                                بدون معوقه
                            </strong>

                            <span class="customer-financial-card-meta success-text">
                        پرداخت منظم است
                    </span>

                        @endif

                    </div>

                </div>

            </div>

        </section>





        {{-- =====================================================
             عملیات سریع
             مرحله بعد تکمیل می‌شود
             ===================================================== --}}


        <section class="customer-quick-actions-section">


            <div class="customer-quick-actions-title">
                <h2>عملیات سریع</h2>
            </div>


            <div class="customer-quick-actions">


                {{-- 1. واریز به پس‌انداز --}}
                <a href="{{ route('customer.savings.deposit.create') }}"
                   class="customer-quick-action">

        <span class="customer-quick-action-icon">
            <i class="bi bi-plus-circle-fill"></i>
        </span>

                    <span class="customer-quick-action-text">
            واریز به
            پس‌انداز
        </span>

                </a>


                {{-- 2. برداشت از پس‌انداز --}}
                <a href="{{ route('customer.savings.withdrawal.create') }}"
                   class="customer-quick-action">

        <span class="customer-quick-action-icon">
            <i class="bi bi-arrow-return-left"></i>
        </span>

                    <span class="customer-quick-action-text">
            برداشت از
            پس‌انداز
        </span>

                </a>


                {{-- 3. گردش حساب --}}
                <a href="{{ route('customer.savings.transactions') }}"
                   class="customer-quick-action">

        <span class="customer-quick-action-icon">
            <i class="bi bi-receipt"></i>
        </span>

                    <span class="customer-quick-action-text">
            گردش
            حساب
        </span>

                </a>


                {{-- 4. درخواست وام --}}
                <a href="{{ route('customer.loan-request.create') }}"
                   class="customer-quick-action">

        <span class="customer-quick-action-icon">
            <i class="bi bi-folder-plus"></i>
        </span>

                    <span class="customer-quick-action-text">
            درخواست
            وام
        </span>

                </a>


                {{-- 5. درخواست‌های وام من --}}
                <a href="{{ url('/customer/loan-requests') }}"
                   class="customer-quick-action">

        <span class="customer-quick-action-icon">
            <i class="bi bi-list-check"></i>
        </span>

                    <span class="customer-quick-action-text">
            درخواست‌های
            وام من
        </span>

                </a>


                {{-- 6. پرداخت قسط --}}
                <a href="{{ route('customer.installments.index') }}"
                   class="customer-quick-action">

        <span class="customer-quick-action-icon">
            <i class="bi bi-journal-text"></i>
        </span>

                    <span class="customer-quick-action-text">
            پرداخت
            قسط
        </span>

                </a>


                {{-- 7. پرداخت قسط دیگران --}}
                <a href="{{ route('customer.installments.others.create') }}"
                   class="customer-quick-action">

        <span class="customer-quick-action-icon">
            <i class="bi bi-people-fill"></i>
        </span>

                    <span class="customer-quick-action-text">
            پرداخت قسط
            دیگران
        </span>

                </a>


                {{-- 8. واریز به پس‌انداز دیگران --}}
                <a href="{{ route('customer.savings-transfer.create') }}"
                   class="customer-quick-action">

        <span class="customer-quick-action-icon">
            <i class="bi bi-person-plus-fill"></i>
        </span>

                    <span class="customer-quick-action-text">
            واریز به
            پس‌انداز دیگران
        </span>

                </a>


            </div>


        </section>


        {{-- =========================================================
     DASHBOARD - LOAN & TRANSACTIONS
     ========================================================= --}}

        <div class="customer-dashboard-bottom-grid">

            {{-- =====================================================
                 آخرین تراکنش‌ها
                 ===================================================== --}}
            <section class="customer-transactions-section">

                <div class="customer-transactions-header">

                    <h2>
                        آخرین تراکنش‌ها
                    </h2>

                    <a href="{{ route('customer.savings.transactions') }}"
                       class="customer-transactions-all">
                        مشاهده همه
                        <i class="bi bi-arrow-left"></i>
                    </a>

                </div>


                <div class="customer-transactions-table-wrapper">

                    @if($latestTransactions->isNotEmpty())

                        <table class="customer-transactions-table">

                            <thead>
                            <tr>
                                <th>تاریخ</th>
                                <th>نوع تراکنش</th>
                                <th>مبلغ</th>
                                <th>وضعیت</th>
                            </tr>
                            </thead>

                            <tbody>

                            @foreach($latestTransactions as $transaction)

                                @php
                                    $isDeposit = $transaction->transaction_type === \App\Enums\TransactionType::DEPOSIT;

                                    $isWithdrawal = $transaction->transaction_type === \App\Enums\TransactionType::WITHDRAWAL;

                                    $isInstallment = $transaction->transaction_type === \App\Enums\TransactionType::INSTALLMENT_PAYMENT;

                                    $isPositive = $isDeposit;

                                    $iconClass = match ($transaction->transaction_type) {
                                        \App\Enums\TransactionType::DEPOSIT => 'deposit',
                                        \App\Enums\TransactionType::WITHDRAWAL => 'withdrawal',
                                        \App\Enums\TransactionType::INSTALLMENT_PAYMENT => 'payment',
                                        default => 'transfer',
                                    };

                                    $icon = match ($transaction->transaction_type) {
                                        \App\Enums\TransactionType::DEPOSIT => 'bi-arrow-down',
                                        \App\Enums\TransactionType::WITHDRAWAL => 'bi-arrow-up',
                                        \App\Enums\TransactionType::INSTALLMENT_PAYMENT => 'bi-arrow-up',
                                        \App\Enums\TransactionType::TRANSFER => 'bi-arrow-left-right',
                                        \App\Enums\TransactionType::ADJUSTMENT => 'bi-pencil',
                                    };
                                @endphp

                                <tr>

                                    <td>
                                        {{ $transaction->jalali_transaction_date }}
                                    </td>

                                    <td>

                                        <div class="customer-transaction-type">

                                <span class="customer-transaction-icon {{ $iconClass }}">
                                    <i class="bi {{ $icon }}"></i>
                                </span>

                                            <span>
                                    {{ $transaction->transaction_type->label() }}
                                </span>

                                        </div>

                                    </td>

                                    <td class="customer-transaction-amount {{ $isPositive ? 'positive' : 'negative' }}">

                                        {{ number_format($transaction->amount) }}

                                        ریال

                                    </td>

                                    <td>

                            <span class="customer-transaction-status success">
                                موفق
                            </span>

                                    </td>

                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    @else

                        <div class="customer-transactions-empty">

                            <i class="bi bi-receipt"></i>

                            <span>
                    هنوز تراکنشی ثبت نشده است.
                </span>

                        </div>

                    @endif

                </div>

            </section>



            {{-- =====================================================
     وام فعال شما
     ===================================================== --}}
            <section class="customer-active-loan-card">

                <div class="customer-active-loan-header">

                    <div class="customer-active-loan-title">

            <span class="customer-active-loan-title-icon">
                <i class="bi bi-cash-coin"></i>
            </span>

                        <h2>
                            وام فعال شما
                        </h2>

                    </div>

                    @if($activeLoan)

                        <span class="customer-active-loan-status">
                فعال
            </span>

                    @endif

                </div>


                @if($activeLoan)

                    {{-- نام نوع وام --}}
                    <div class="customer-active-loan-name">
                        {{ $activeLoan->loanType->name }}
                    </div>

                    @php
                        $totalInstallments = $activeLoan->installments->count();

                        $paidInstallments = $activeLoan->installments
                            ->where('status', \App\Enums\InstallmentStatus::PAID)
                            ->count();

                        $remainingInstallments = $totalInstallments - $paidInstallments;

                        $paidAmount = $activeLoan->installments
                            ->where('status', \App\Enums\InstallmentStatus::PAID)
                            ->sum('amount');

                        $remainingAmount = $activeLoan->loan_amount - $paidAmount;

                        $progress = $activeLoan->loan_amount > 0
                            ? round(($paidAmount / $activeLoan->loan_amount) * 100)
                            : 0;

                        $nextInstallment = $activeLoan->installments
                            ->firstWhere('status', \App\Enums\InstallmentStatus::PENDING);
                    @endphp


                    {{-- پیشرفت پرداخت --}}
                    <div class="customer-loan-progress-row">

                        <div class="customer-loan-progress">

                            <div class="customer-loan-progress-bar"
                                 style="width: {{ $progress }}%;">
                            </div>

                        </div>

                        <span class="customer-loan-progress-value">
                {{ $progress }}٪
            </span>

                    </div>


                    {{-- اطلاعات وام --}}
                    <div class="customer-active-loan-info">

                        {{-- مبلغ وام --}}
                        <div class="customer-loan-info-row">

                <span class="customer-loan-info-label">
                    مبلغ وام
                </span>

                            <strong class="customer-loan-info-value">
                                {{ number_format($activeLoan->loan_amount) }}
                                ریال
                            </strong>

                        </div>


                        {{-- باقی مانده --}}
                        <div class="customer-loan-info-row">

                <span class="customer-loan-info-label">
                    باقی‌مانده
                </span>

                            <strong class="customer-loan-info-value">
                                {{ number_format($remainingAmount) }}
                                ریال
                            </strong>

                        </div>


                        {{-- اقساط باقی مانده --}}
                        <div class="customer-loan-info-row">

                <span class="customer-loan-info-label">
                    اقساط باقی‌مانده
                </span>

                            <strong class="customer-loan-info-value">
                                {{ $remainingInstallments }}
                                قسط
                            </strong>

                        </div>


                        {{-- قسط بعدی --}}
                        <div class="customer-loan-info-row">

                <span class="customer-loan-info-label">
                    قسط بعدی
                </span>

                            @if($nextInstallment)

                                <strong class="customer-loan-info-value">
                                    {{ jdate($nextInstallment->due_date)->format('Y/m/d') }}
                                </strong>

                            @else

                                <strong class="customer-loan-info-value">
                                    ندارد
                                </strong>

                            @endif

                        </div>


                        {{-- شماره وام --}}
                        <div class="customer-loan-info-row">

                <span class="customer-loan-info-label">
                    شماره وام
                </span>

                            <strong class="customer-loan-info-value" dir="ltr">
                                {{ $activeLoan->full_loan_number }}
                            </strong>

                        </div>

                    </div>


                    {{-- مشاهده جزئیات --}}
                    <a href="{{ route('customer.installments.index') }}"
                       class="customer-active-loan-details-button">

                        مشاهده اقساط وام

                        <i class="bi bi-arrow-left"></i>

                    </a>


                @else

                    <div class="text-muted text-center py-4">
                        <i class="bi bi-cash-stack fs-2 d-block mb-2"></i>

                        در حال حاضر وام فعالی ندارید.
                    </div>

                @endif

            </section>

        </div>


        {{-- =========================================================
     کمک‌ها
     ========================================================= --}}
        <section class="customer-donations-section">

            <div class="customer-donations-title">
                <h2>کمک‌ها</h2>
            </div>


            <div class="customer-donations-actions">

                @forelse($donationAccounts as $account)

                    <a href="{{ route('customer.donations.create', ['account_id' => $account->id]) }}"
                       class="customer-donation-action text-decoration-none">

                <span class="customer-donation-action-icon">

                    <i class="bi bi-heart-fill"></i>

                </span>


                        <span class="customer-donation-action-text">

                    {{ $account->name }}

                </span>

                    </a>

                @empty

                    <div class="customer-donations-empty">

                        <i class="bi bi-heart"></i>

                        <span>
                    در حال حاضر گزینه‌ای برای کمک فعال نیست.
                </span>

                    </div>

                @endforelse

            </div>

        </section>


    </div>

@endsection

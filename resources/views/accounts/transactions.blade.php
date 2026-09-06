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
                                {{ number_format($account->balance) }}
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

                                    @if($transaction->transaction_type === \App\Enums\TransactionType::DEPOSIT)

                                        <span class="transaction-type deposit">
                                        <span class="transaction-type-dot"></span>
                                        <i class="bi bi-arrow-down-circle"></i>
                                        {{ $transaction->transaction_type->label() }}
                                    </span>

                                    @elseif($transaction->transaction_type === \App\Enums\TransactionType::WITHDRAWAL)

                                        <span class="transaction-type withdrawal">
                                        <span class="transaction-type-dot"></span>
                                        <i class="bi bi-arrow-up-circle"></i>
                                        {{ $transaction->transaction_type->label() }}
                                    </span>

                                    @elseif($transaction->transaction_type === \App\Enums\TransactionType::ADJUSTMENT)

                                        <span class="transaction-type adjustment">
                                        <span class="transaction-type-dot"></span>
                                        <i class="bi bi-pencil-square"></i>
                                        {{ $transaction->transaction_type->label() }}
                                    </span>

                                    @else

                                        <span class="transaction-type other">
                                        <span class="transaction-type-dot"></span>
                                        <i class="bi bi-arrow-left-right"></i>
                                        {{ $transaction->transaction_type->label() }}
                                    </span>

                                    @endif

                                </td>


                                {{-- مبلغ --}}
                                <td>

                                    <div class="transaction-amount">

                                        <strong>
                                            {{ number_format($transaction->amount) }}
                                        </strong>

                                        <span>ریال</span>

                                    </div>

                                </td>


                                {{-- مانده --}}
                                <td>

                                    <div class="transaction-balance">

                                        <strong>
                                            {{ number_format($transaction->balance_after) }}
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

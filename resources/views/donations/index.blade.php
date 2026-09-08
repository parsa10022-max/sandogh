@extends('layouts.app')

@section('title', 'لیست کمک‌ها')

@push('styles')
    @vite('resources/css/admin/donation/index.css')
@endpush

@section('content')


    <div class="donation-list-page">

        {{-- Header --}}
        <div class="donation-list-header">

            <div class="donation-list-header__content">

                <div class="donation-list-header__icon">
                    <i class="bi bi-heart-fill"></i>
                </div>

                <div>

                    <h1 class="donation-list-header__title">
                        لیست کمک‌های ثبت شده
                    </h1>

                    <p class="donation-list-header__subtitle">
                        کمک‌های ثبت‌شده و تراکنش‌های مربوط به حساب‌های صندوق
                    </p>

                </div>

            </div>

        </div>


        {{-- System Accounts --}}
        <div class="donation-accounts-card">

            <div class="donation-section-header">

                <div class="donation-section-header__icon">
                    <i class="bi bi-bank"></i>
                </div>

                <div>

                    <h2 class="donation-section-header__title">
                        حساب‌های سیستمی
                    </h2>

                    <p class="donation-section-header__subtitle">
                        برای مشاهده تراکنش‌های هر حساب انتخاب کنید.
                    </p>

                </div>

            </div>


            <div class="donation-accounts-body">

                <div class="donation-accounts-grid">

                    @forelse($accounts as $account)

                        <div class="donation-account-item">

                            <a href="{{ route('accounts.transactions', $account) }}"
                               class="donation-account-card">

                                <div class="donation-account-card__top">

                                    <div class="donation-account-card__name">

                                    <span class="donation-account-card__icon">
                                        <i class="bi bi-wallet2"></i>
                                    </span>

                                        <span>
                                        {{ $account->name }}
                                    </span>

                                    </div>

                                    <span class="donation-account-card__number"
                                          dir="ltr">

                                    {{ $account->account_number }}

                                </span>

                                </div>


                                <div class="donation-account-card__divider"></div>


                                <div class="donation-account-card__bottom">

                                <span class="donation-account-card__balance-label">
                                    موجودی
                                </span>

                                    <strong class="donation-account-card__balance">

                                        {{ number_format($account->balance) }}

                                        <small>ریال</small>

                                    </strong>

                                </div>


                                <div class="donation-account-card__arrow">

                                    <i class="bi bi-chevron-left"></i>

                                </div>

                            </a>

                        </div>

                    @empty

                        <div class="donation-accounts-empty">

                            <i class="bi bi-bank"></i>

                            <span>
                            حساب سیستمی ثبت نشده است.
                        </span>

                        </div>

                    @endforelse

                </div>

            </div>

        </div>


        {{-- Transactions --}}
        <div class="donation-transactions-card">

            <div class="donation-section-header">

                <div class="donation-section-header__icon
                        donation-section-header__icon--transactions">

                    <i class="bi bi-receipt"></i>

                </div>

                <div>

                    <h2 class="donation-section-header__title">
                        تراکنش‌های کمک
                    </h2>

                    <p class="donation-section-header__subtitle">
                        فهرست تراکنش‌های ثبت‌شده در حساب‌های صندوق
                    </p>

                </div>

            </div>


            <div class="donation-transactions-body">

                <div class="donation-table-wrapper">

                    <table class="donation-transactions-table">

                        <thead>

                        <tr>

                            <th>
                                تاریخ
                            </th>

                            <th>
                                عنوان حساب
                            </th>

                            <th>
                                شماره حساب
                            </th>

                            <th>
                                مبلغ
                            </th>

                            <th>
                                ثبت کننده
                            </th>

                            <th>
                                توضیحات
                            </th>

                        </tr>

                        </thead>


                        <tbody>

                        @forelse($transactions as $transaction)

                            <tr>

                                <td>

                                <span class="donation-date">

                                    <i class="bi bi-calendar3"></i>

                                    {{ \Morilog\Jalali\Jalalian::fromDateTime(
                                        $transaction->transaction_date
                                    )->format('Y/m/d') }}

                                </span>

                                </td>


                                <td>

                                <span class="donation-transaction-account">

                                    <span class="donation-transaction-account__icon">
                                        <i class="bi bi-wallet2"></i>
                                    </span>

                                    <strong>
                                        {{ $transaction->account->name ?? '-' }}
                                    </strong>

                                </span>

                                </td>


                                <td>

                                <span class="donation-transaction-number"
                                      dir="ltr">

                                    {{ $transaction->account->account_number ?? '-' }}

                                </span>

                                </td>


                                <td>

                                <span class="donation-transaction-amount">

                                    {{ number_format($transaction->amount) }}

                                    <small>ریال</small>

                                </span>

                                </td>


                                <td>

                                <span class="donation-creator">

                                    <i class="bi bi-person"></i>

                                    {{ $transaction->creator->name ?? '-' }}

                                </span>

                                </td>


                                <td>

                                <span class="donation-description">

                                    {{ $transaction->description ?? '-' }}

                                </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6">

                                    <div class="donation-transactions-empty">

                                        <div class="donation-transactions-empty__icon">
                                            <i class="bi bi-receipt"></i>
                                        </div>

                                        <strong>
                                            موردی ثبت نشده است.
                                        </strong>

                                        <span>
                                        هنوز تراکنشی برای نمایش وجود ندارد.
                                    </span>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>


                @if($transactions->hasPages())

                    <div class="donation-pagination">

                        {{ $transactions->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>


@endsection

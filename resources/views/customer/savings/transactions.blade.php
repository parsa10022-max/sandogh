
@extends('customer.layouts.app')

@section('title', 'گردش حساب پس‌انداز')
@section('header_title', 'گردش حساب پس‌انداز')
@section('header_subtitle', 'مشاهده تراکنش‌های حساب پس‌انداز')

@section('content')

    <div class="container-fluid customer-savings-transactions-page">

        @if(!$account)

            <div class="customer-savings-transactions-empty">

                <div class="customer-savings-transactions-empty-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <h5>
                    حساب پس‌اندازی ندارید
                </h5>

                <p>
                    در حال حاضر حساب پس‌انداز فعالی برای شما ثبت نشده است.
                </p>

            </div>

        @else

            {{-- =====================================================
                 خلاصه حساب
            ====================================================== --}}

            <section class="customer-savings-account-summary">

                <div class="customer-savings-section-title">

                    <div class="customer-savings-section-icon">
                        <i class="bi bi-wallet2"></i>
                    </div>

                    <div>
                        <h2>
                            حساب پس‌انداز
                        </h2>

                        <span>
                            اطلاعات و وضعیت حساب شما
                        </span>
                    </div>

                </div>


                <div class="customer-savings-account-grid">

                    {{-- شماره حساب --}}
                    <div class="customer-savings-account-item">

                        <span>
                            شماره حساب
                        </span>

                        <strong dir="ltr">
                            {{ $account->account_number }}
                        </strong>

                    </div>


                    {{-- موجودی --}}
                    <div class="customer-savings-account-item balance">

                        <span>
                            موجودی فعلی
                        </span>

                        <strong>
                            {{ number_format($account->balance) }}
                            <small>ریال</small>
                        </strong>

                    </div>


                    {{-- تعداد تراکنش --}}
                    <div class="customer-savings-account-item">

                        <span>
                            تعداد تراکنش‌ها
                        </span>

                        <strong>
                            {{ $transactions->total() }}
                        </strong>

                    </div>

                </div>

            </section>


            {{-- =====================================================
                 گردش حساب
            ====================================================== --}}

            <section class="customer-savings-transactions-section">

                <div class="customer-savings-section-title">

                    <div class="customer-savings-section-icon">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>

                    <div>
                        <h2>
                            گردش حساب
                        </h2>

                        <span>
                            آخرین تراکنش‌های حساب پس‌انداز
                        </span>
                    </div>

                </div>

                {{-- =================================================
     FILTERS
================================================== --}}

                <div class="customer-savings-transactions-filter-box">

                    {{-- عنوان فیلتر --}}
                    <button
                        type="button"
                        class="customer-savings-filter-toggle"
                        data-bs-toggle="collapse"
                        data-bs-target="#transactionsFilter"
                        aria-expanded="{{ request()->hasAny([
        'transaction_no',
        'transaction_type',
        'from_date',
        'to_date',
        'amount'
    ]) ? 'true' : 'false' }}"
                        aria-controls="transactionsFilter"
                    >

        <span>
            <i class="bi bi-funnel"></i>
            فیلتر گردش حساب
        </span>

                        <i class="bi bi-chevron-down filter-toggle-icon"></i>

                    </button>


                    {{-- محتوای فیلتر --}}
                    <div
                        class="collapse {{ request()->hasAny([
                       'transaction_no',
                                'transaction_type',
                                'from_date',
                                'to_date',
                                'amount'
                            ]) ? 'show' : '' }}"
                        id="transactionsFilter"
                    >

                        <div class="customer-savings-transactions-filter-content">

                            <form
                                method="GET"
                                action="{{ route('customer.savings.transactions') }}"
                                class="customer-savings-transactions-filters"
                            >

                                <div class="customer-savings-filter-grid">

                                    {{-- شماره تراکنش --}}
                                    <div class="customer-savings-filter-item">

                                        <label for="transaction_no">
                                            <i class="bi bi-search"></i>
                                            شماره تراکنش
                                        </label>

                                        <input
                                            type="text"
                                            id="transaction_no"
                                            name="transaction_no"
                                            value="{{ request('transaction_no') }}"
                                            placeholder="شماره تراکنش"
                                            dir="ltr"
                                        >

                                    </div>


                                    {{-- نوع تراکنش --}}
                                    <div class="customer-savings-filter-item">

                                        <label for="transaction_type">
                                            <i class="bi bi-arrow-left-right"></i>
                                            نوع تراکنش
                                        </label>

                                        <select
                                            id="transaction_type"
                                            name="transaction_type"
                                        >

                                            <option
                                                value="all"
                                                {{ request('transaction_type', 'all') === 'all' ? 'selected' : '' }}
                                            >
                                                همه
                                            </option>

                                            <option
                                                value="1"
                                                {{ request('transaction_type') === '1' ? 'selected' : '' }}
                                            >
                                                واریز
                                            </option>

                                            <option
                                                value="2"
                                                {{ request('transaction_type') === '2' ? 'selected' : '' }}
                                            >
                                                برداشت
                                            </option>

                                        </select>

                                    </div>


                                    {{-- از تاریخ --}}
                                    {{-- از تاریخ --}}
                                    <div class="customer-savings-filter-item">

                                        <x-inputs.date-input
                                            name="from_date"
                                            label="از تاریخ"
                                            :value="request('from_date')"
                                        />

                                    </div>


                                    {{-- تا تاریخ --}}
                                    <div class="customer-savings-filter-item">

                                        <x-inputs.date-input
                                            name="to_date"
                                            label="تا تاریخ"
                                            :value="request('to_date')"
                                        />

                                    </div>


                                    {{-- مبلغ --}}
                                    <div class="customer-savings-filter-item">

                                        <label for="amount">
                                            <i class="bi bi-cash-stack"></i>
                                            مبلغ
                                        </label>

                                        <input
                                            type="text"
                                            id="amount"
                                            name="amount"
                                            value="{{ request('amount') }}"
                                            placeholder="مثلاً 50,000,000"
                                            inputmode="numeric"
                                            autocomplete="off"
                                            dir="ltr"
                                            class="js-money-input"
                                        >

                                    </div>


                                    {{-- دکمه‌ها --}}
                                    <div class="customer-savings-filter-actions">

                                        <button
                                            type="submit"
                                            class="customer-savings-filter-submit"
                                        >
                                            <i class="bi bi-funnel"></i>
                                            اعمال فیلتر
                                        </button>


                                        <a
                                            href="{{ route('customer.savings.transactions') }}"
                                            class="customer-savings-filter-reset"
                                        >
                                            <i class="bi bi-x-circle"></i>
                                            حذف فیلتر
                                        </a>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

                {{-- =================================================
                     MOBILE
                     نمایش تراکنش‌ها به صورت کارت
                ================================================== --}}

                <div class="customer-savings-transactions-mobile">

                    @forelse($transactions as $transaction)

                        <article class="customer-savings-transaction-card">

                            <div class="customer-savings-transaction-top">

                                <div class="customer-savings-transaction-type">

                                    @if($transaction->transaction_type->value == 1)

                                        <span class="deposit">
                                            <i class="bi bi-arrow-down-circle"></i>
                                            واریز
                                        </span>

                                    @else

                                        <span class="withdrawal">
                                            <i class="bi bi-arrow-up-circle"></i>
                                            برداشت
                                        </span>

                                    @endif

                                </div>

                                <span class="customer-savings-transaction-date">

                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($transaction->transaction_date)->format('Y/m/d') }}

                                </span>

                            </div>


                            <div class="customer-savings-transaction-middle">

                                <div>

                                    <span class="transaction-label">
                                        شماره تراکنش
                                    </span>

                                    <strong dir="ltr">
                                        {{ $transaction->transaction_no }}
                                    </strong>

                                </div>


                                <div class="transaction-amount">

                                    @if($transaction->transaction_type->value == 1)

                                        <strong class="deposit">
                                            +
                                            {{ number_format($transaction->amount) }}
                                            <small>ریال</small>
                                        </strong>

                                    @else

                                        <strong class="withdrawal">
                                            -
                                            {{ number_format($transaction->amount) }}
                                            <small>ریال</small>
                                        </strong>

                                    @endif

                                </div>

                            </div>


                            <div class="customer-savings-transaction-bottom">

                                <div>

                                    <span class="transaction-label">
                                        مانده حساب
                                    </span>

                                    <strong>
                                        {{ number_format($transaction->balance_after) }}
                                        <small>ریال</small>
                                    </strong>

                                </div>


                                <div class="transaction-description">

                                    <span class="transaction-label">
                                        توضیحات
                                    </span>

                                    <span>
                                        {{ $transaction->description ?? '-' }}
                                    </span>

                                </div>

                            </div>

                        </article>

                    @empty

                        <div class="customer-savings-transactions-empty">

                            <div class="customer-savings-transactions-empty-icon">
                                <i class="bi bi-receipt"></i>
                            </div>

                            <h5>
                                تراکنشی ثبت نشده است
                            </h5>

                            <p>
                                هنوز تراکنشی برای حساب پس‌انداز شما ثبت نشده است.
                            </p>

                        </div>

                    @endforelse

                </div>


                {{-- =================================================
                     TABLET / DESKTOP
                ================================================== --}}

                <div class="customer-savings-transactions-table">

                    <div class="table-responsive">

                        <table class="table align-middle mb-0">

                            <thead>

                            <tr>

                                <th>
                                    شماره تراکنش
                                </th>

                                <th>
                                    تاریخ
                                </th>

                                <th>
                                    نوع
                                </th>

                                <th class="text-center">
                                    مبلغ
                                </th>

                                <th class="text-center">
                                    مانده
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

                                        <span dir="ltr">
                                            {{ $transaction->transaction_no }}
                                        </span>

                                    </td>


                                    <td>

                                        {{ \Morilog\Jalali\Jalalian::fromDateTime($transaction->transaction_date)->format('Y/m/d') }}

                                    </td>


                                    <td>

                                        @if($transaction->transaction_type->value == 1)

                                            <span class="customer-savings-transaction-badge deposit">

                                                <i class="bi bi-arrow-down-circle"></i>

                                                واریز

                                            </span>

                                        @else

                                            <span class="customer-savings-transaction-badge withdrawal">

                                                <i class="bi bi-arrow-up-circle"></i>

                                                برداشت

                                            </span>

                                        @endif

                                    </td>


                                    <td class="text-center">

                                        @if($transaction->transaction_type->value == 1)

                                            <strong class="customer-savings-amount deposit">

                                                +
                                                {{ number_format($transaction->amount) }}

                                            </strong>

                                        @else

                                            <strong class="customer-savings-amount withdrawal">

                                                -
                                                {{ number_format($transaction->amount) }}

                                            </strong>

                                        @endif

                                        <small>
                                            ریال
                                        </small>

                                    </td>


                                    <td class="text-center">

                                        <strong class="customer-savings-balance">

                                            {{ number_format($transaction->balance_after) }}

                                        </strong>

                                        <small>
                                            ریال
                                        </small>

                                    </td>


                                    <td>

                                        {{ $transaction->description ?? '-' }}

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="6"
                                        class="customer-savings-table-empty"
                                    >

                                        <i class="bi bi-receipt"></i>

                                        تراکنشی ثبت نشده است.

                                    </td>

                                </tr>

                            @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>


                {{-- =================================================
                     PAGINATION
                ================================================== --}}

                @if($transactions->hasPages())

                    <div class="customer-savings-transactions-pagination">

                        {{ $transactions->links() }}

                    </div>

                @endif

            </section>

        @endif

    </div>

@endsection


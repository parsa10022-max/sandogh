@extends('layouts.app')

@section('title', 'حساب‌ها')

@section('content')

    
    <div class="container-fluid accounts-page">

        {{-- جستجو و آمار --}}
        <div class="card accounts-toolbar-card mb-3">

            <div class="card-body">

                <div class="accounts-toolbar" dir="rtl">

                    {{-- جستجو --}}
                    <div class="accounts-search-box">

                        <form method="GET">

                            <label class="accounts-search-label">
                                <i class="bi bi-search"></i>
                                جستجوی حساب
                            </label>

                            <div class="input-group accounts-search-group">

                                <input
                                    type="text"
                                    name="search"
                                    class="form-control accounts-search-input"
                                    placeholder="شماره حساب، نام، کد ملی..."
                                    value="{{ request('search') }}"
                                >

                                <button
                                    type="submit"
                                    class="btn accounts-search-btn"
                                    aria-label="جستجو"
                                >
                                    <i class="bi bi-search"></i>
                                </button>

                            </div>

                        </form>

                    </div>


                    {{-- آمار --}}
                    <div class="accounts-statistics">

                        <div class="accounts-stat-card accounts-stat-card-purple">

                            <div class="accounts-stat-icon">
                                <i class="bi bi-wallet2"></i>
                            </div>

                            <div class="accounts-stat-content">

                            <span class="accounts-stat-label">
                                تعداد حساب‌ها
                            </span>

                                <strong class="accounts-stat-value">
                                    {{ number_format($totalAccounts) }}
                                </strong>

                            </div>

                        </div>


                        <div class="accounts-stat-card accounts-stat-card-blue">

                            <div class="accounts-stat-icon">
                                <i class="bi bi-cash-stack"></i>
                            </div>

                            <div class="accounts-stat-content">

                            <span class="accounts-stat-label">
                                موجودی کل
                            </span>

                                <strong class="accounts-stat-value">
                                    {{ number_format($totalBalance) }}
                                    <small>ریال</small>
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- لیست حساب‌ها --}}
        <div class="card accounts-list-card">

            <div class="card-header accounts-list-header">

                <div class="accounts-title-wrapper">

                    <div class="accounts-title-icon">
                        <i class="bi bi-bank"></i>
                    </div>

                    <div>

                        <h5 class="accounts-title">
                            لیست حساب‌ها
                        </h5>

                        <p class="accounts-subtitle">
                            حساب‌های مشتریان و حساب‌های سیستمی صندوق
                        </p>

                    </div>

                </div>

                <span class="accounts-count">
                {{ number_format($accounts->total()) }}
                حساب
            </span>

            </div>


            <div class="card-body accounts-list-body">

                <div class="table-responsive accounts-table-wrapper">

                    <table class="table align-middle accounts-table">

                        <thead>

                        <tr>
                            <th>شماره حساب</th>
                            <th>کد مشتری</th>
                            <th>نام حساب / صاحب حساب</th>
                            <th>موجودی</th>
                            <th>وضعیت</th>
                            <th class="text-center">عملیات</th>
                        </tr>

                        </thead>


                        <tbody>

                        @forelse($accounts as $account)

                            <tr class="{{ $account->customer ? '' : 'accounts-system-row' }}">

                                {{-- شماره حساب --}}
                                <td>

                                    <div class="accounts-number">

                                    <span class="{{ $account->customer ? 'accounts-customer-icon' : 'accounts-system-icon' }}">

                                        <i class="bi {{ $account->customer ? 'bi-person-fill' : 'bi-bank2' }}"></i>

                                    </span>

                                        <strong>
                                            {{ $account->account_number }}
                                        </strong>

                                    </div>

                                </td>


                                {{-- کد مشتری --}}
                                <td class="text-center">

                                    @if($account->customer)

                                        <span class="accounts-customer-code">
                                        {{ $account->customer->customer_code }}
                                    </span>

                                    @else

                                        <span class="accounts-muted">-</span>

                                    @endif

                                </td>


                                {{-- نام حساب / صاحب حساب --}}
                                <td>

                                    <div class="accounts-owner">

                                        @if($account->customer)

                                            <span class="accounts-type-badge customer">
                                            <i class="bi bi-person-fill"></i>
                                            مشتری
                                        </span>

                                            <strong class="accounts-owner-name">
                                                {{ $account->customer->first_name }}
                                                {{ $account->customer->last_name }}
                                            </strong>

                                        @else

                                            <span class="accounts-type-badge system">
                                            <i class="bi bi-bank2"></i>
                                            سیستمی
                                        </span>

                                            <strong class="accounts-owner-name">
                                                {{ $account->name }}
                                            </strong>

                                        @endif

                                    </div>

                                </td>


                                {{-- موجودی --}}
                                <td>

                                    <div class="accounts-balance">

                                        <strong>
                                            {{ number_format($account->balance) }}
                                        </strong>

                                        <span>ریال</span>

                                    </div>

                                </td>


                                {{-- وضعیت --}}
                                <td>

                                    @if($account->status === \App\Enums\AccountStatus::ACTIVE)

                                        <span class="accounts-status active">

                                        <span class="accounts-status-dot"></span>

                                        <i class="bi bi-check-circle"></i>

                                        فعال

                                    </span>

                                    @else

                                        <span class="accounts-status inactive">

                                        <span class="accounts-status-dot"></span>

                                        <i class="bi bi-x-circle"></i>

                                        غیرفعال

                                    </span>

                                    @endif

                                </td>


                                {{-- عملیات --}}
                                <td class="text-center">

                                    <a
                                        href="{{ route('accounts.show', $account) }}"
                                        class="btn accounts-view-btn"
                                        title="مشاهده حساب"
                                    >

                                        <i class="bi bi-eye"></i>

                                        <span>مشاهده</span>

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6">

                                    <div class="accounts-empty">

                                        <div class="accounts-empty-icon">
                                            <i class="bi bi-wallet2"></i>
                                        </div>

                                        <h6>
                                            حسابی پیدا نشد
                                        </h6>

                                        <p>
                                            برای عبارت جستجوی واردشده حسابی وجود ندارد.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- صفحه‌بندی --}}
                @if($accounts->hasPages())

                    <div class="accounts-pagination">

                        {{ $accounts->withQueryString()->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

@endsection

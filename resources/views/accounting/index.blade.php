@extends('layouts.app')

@section('title', 'حسابداری')

@push('styles')
    @vite('resources/css/admin/accounting/index.css')
@endpush

@section('content')

    <div class="container-fluid accounting-dashboard-page">

        {{-- Header --}}
        <div class="accounting-dashboard-header">

            <div class="accounting-dashboard-title-wrapper">

                <div class="accounting-dashboard-title-icon">
                    <i class="bi bi-calculator"></i>
                </div>

                <div>
                    <h1 class="accounting-dashboard-title">
                        حسابداری
                    </h1>

                    <div class="accounting-dashboard-subtitle">
                        عملیات مالی در انتظار ثبت حسابداری
                    </div>
                </div>

            </div>

            <div class="accounting-dashboard-pending">

                <i class="bi bi-hourglass-split"></i>

                <span>{{ $totalCount }} عملیات در انتظار</span>

            </div>

        </div>


        {{-- Statistics --}}
        <div class="accounting-dashboard-statistics">


            {{-- Savings deposits --}}
            <div class="accounting-stat-card">

                <div class="accounting-stat-main">

                    <div class="accounting-stat-icon accounting-stat-icon-primary">
                        <i class="bi bi-wallet2"></i>
                    </div>

                    <div class="accounting-stat-content">

                        <div class="accounting-stat-label">
                            واریز پس‌انداز
                        </div>

                        <div class="accounting-stat-value">
                            {{ $savingsTransfersOwnCount + $savingsTransfersOtherCount }}
                        </div>

                    </div>

                </div>

                <a href="{{ route('reports.accounting.savings-transfers') }}"
                   class="accounting-stat-link">

                    <span>مشاهده واریزها</span>
                    <i class="bi bi-arrow-left"></i>

                </a>

            </div>


            {{-- Savings withdrawals --}}
            <div class="accounting-stat-card">

                <div class="accounting-stat-main">

                    <div class="accounting-stat-icon accounting-stat-icon-danger">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                    <div class="accounting-stat-content">

                        <div class="accounting-stat-label">
                            برداشت پس‌انداز
                        </div>

                        <div class="accounting-stat-value">
                            {{ $withdrawalsCount }}
                        </div>

                    </div>

                </div>

                <a href="{{ route('reports.accounting.withdrawals') }}"
                   class="accounting-stat-link accounting-stat-link-danger">

                    <span>مشاهده برداشت‌ها</span>
                    <i class="bi bi-arrow-left"></i>

                </a>

            </div>


            {{-- Loan payments --}}
            <div class="accounting-stat-card">

                <div class="accounting-stat-main">

                    <div class="accounting-stat-icon accounting-stat-icon-success">
                        <i class="bi bi-credit-card"></i>
                    </div>

                    <div class="accounting-stat-content">

                        <div class="accounting-stat-label">
                            پرداخت اقساط
                        </div>

                        <div class="accounting-stat-value">
                            {{ $loanPaymentsOwnCount + $loanPaymentsOtherCount }}
                        </div>

                    </div>

                </div>

                <a href="{{ route('reports.accounting.loan-payments') }}"
                   class="accounting-stat-link accounting-stat-link-success">

                    <span>مشاهده اقساط</span>
                    <i class="bi bi-arrow-left"></i>

                </a>

            </div>


            {{-- Total --}}
            <div class="accounting-stat-card accounting-stat-card-total">

                <div class="accounting-stat-main">

                    <div class="accounting-stat-icon accounting-stat-icon-warning">
                        <i class="bi bi-clipboard-check"></i>
                    </div>

                    <div class="accounting-stat-content">

                        <div class="accounting-stat-label">
                            مجموع عملیات
                        </div>

                        <div class="accounting-stat-value">
                            {{ $totalCount }}
                        </div>

                    </div>

                </div>

                <div class="accounting-stat-total-label">
                    عملیات نیازمند ثبت حسابداری
                </div>

            </div>

        </div>


        {{-- Operation links --}}
        <div class="accounting-operations-card">

            <div class="accounting-operations-header">

                <div class="accounting-operations-title-wrapper">

                    <div class="accounting-operations-icon">
                        <i class="bi bi-list-check"></i>
                    </div>

                    <div>
                        <h2 class="accounting-operations-title">
                            عملیات نیازمند بررسی
                        </h2>

                        <div class="accounting-operations-subtitle">
                            انتخاب عملیات برای بررسی و ثبت در حسابداری
                        </div>
                    </div>

                </div>

            </div>


            <div class="accounting-operations-body">

                <div class="accounting-operation-grid">


                    {{-- Savings transfers --}}
                    <a href="{{ route('reports.accounting.savings-transfers') }}"
                       class="accounting-operation-item">

                        <div class="accounting-operation-icon accounting-operation-icon-primary">
                            <i class="bi bi-wallet2"></i>
                        </div>

                        <div class="accounting-operation-content">

                            <div class="accounting-operation-title">
                                واریز به حساب پس‌انداز
                            </div>

                            <div class="accounting-operation-description">
                                واریز خود و واریز به دیگر اعضا
                            </div>

                        </div>

                        <div class="accounting-operation-count accounting-operation-count-primary">
                            {{ $savingsTransfersOwnCount + $savingsTransfersOtherCount }}
                        </div>

                        <i class="bi bi-chevron-left accounting-operation-arrow"></i>

                    </a>


                    {{-- Withdrawals --}}
                    <a href="{{ route('reports.accounting.withdrawals') }}"
                       class="accounting-operation-item">

                        <div class="accounting-operation-icon accounting-operation-icon-danger">
                            <i class="bi bi-cash-stack"></i>
                        </div>

                        <div class="accounting-operation-content">

                            <div class="accounting-operation-title">
                                برداشت از پس‌انداز
                            </div>

                            <div class="accounting-operation-description">
                                برداشت‌های پرداخت‌شده
                            </div>

                        </div>

                        <div class="accounting-operation-count accounting-operation-count-danger">
                            {{ $withdrawalsCount }}
                        </div>

                        <i class="bi bi-chevron-left accounting-operation-arrow"></i>

                    </a>


                    {{-- Loan payments --}}
                    <a href="{{ route('reports.accounting.loan-payments') }}"
                       class="accounting-operation-item">

                        <div class="accounting-operation-icon accounting-operation-icon-success">
                            <i class="bi bi-credit-card"></i>
                        </div>

                        <div class="accounting-operation-content">

                            <div class="accounting-operation-title">
                                پرداخت اقساط
                            </div>

                            <div class="accounting-operation-description">
                                اقساط خود و اقساط دیگران
                            </div>

                        </div>

                        <div class="accounting-operation-count accounting-operation-count-success">
                            {{ $loanPaymentsOwnCount + $loanPaymentsOtherCount }}
                        </div>

                        <i class="bi bi-chevron-left accounting-operation-arrow"></i>

                    </a>

                </div>

            </div>

        </div>

    </div>

@endsection

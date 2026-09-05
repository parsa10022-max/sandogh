@extends('layouts.app')

@section('title', 'حسابداری')

@section('content')

    <div class="container-fluid accounting-page">

        {{-- Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">

            <div>
                <h1 class="h4 fw-bold mb-1">
                    <i class="bi bi-calculator me-1"></i>
                    حسابداری
                </h1>

                <div class="text-muted small">
                    عملیات مالی در انتظار ثبت حسابداری
                </div>
            </div>

            <div class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill">
                <i class="bi bi-hourglass-split me-1"></i>
                {{ $totalCount }} عملیات در انتظار
            </div>

        </div>


        {{-- Statistics --}}
        <div class="row g-3 mb-4">

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card h-100 border-0 shadow-sm rounded-4">

                    <div class="card-body">

                        <div class="d-flex align-items-center gap-3">

                            <div class="rounded-3 p-3 bg-primary-subtle text-primary">
                                <i class="bi bi-wallet2 fs-4"></i>
                            </div>

                            <div>

                                <div class="text-muted small">
                                    واریز پس‌انداز
                                </div>

                                <div class="fs-4 fw-bold">
                                    {{ $savingsTransfersOwnCount + $savingsTransfersOtherCount }}
                                </div>

                            </div>

                        </div>


                        <div class="mt-3">

                            <a href="{{ route('admin.accounting.savings-transfers') }}"
                               class="small text-decoration-none">

                                مشاهده واریزها

                                <i class="bi bi-arrow-left"></i>

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card h-100 border-0 shadow-sm rounded-4">

                    <div class="card-body">

                        <div class="d-flex align-items-center gap-3">

                            <div class="rounded-3 p-3 bg-danger-subtle text-danger">
                                <i class="bi bi-cash-stack fs-4"></i>
                            </div>

                            <div>

                                <div class="text-muted small">
                                    برداشت پس‌انداز
                                </div>

                                <div class="fs-4 fw-bold">
                                    {{ $withdrawalsCount }}
                                </div>

                            </div>

                        </div>


                        <div class="mt-3">

                            <a href="{{ route('admin.accounting.withdrawals') }}"
                               class="small text-decoration-none">

                                مشاهده برداشت‌ها

                                <i class="bi bi-arrow-left"></i>

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card h-100 border-0 shadow-sm rounded-4">

                    <div class="card-body">

                        <div class="d-flex align-items-center gap-3">

                            <div class="rounded-3 p-3 bg-success-subtle text-success">
                                <i class="bi bi-credit-card fs-4"></i>
                            </div>

                            <div>

                                <div class="text-muted small">
                                    پرداخت اقساط
                                </div>

                                <div class="fs-4 fw-bold">
                                    {{ $loanPaymentsOwnCount + $loanPaymentsOtherCount }}
                                </div>

                            </div>

                        </div>


                        <div class="mt-3">

                            <a href="{{ route('admin.accounting.loan-payments') }}"
                               class="small text-decoration-none">

                                مشاهده اقساط

                                <i class="bi bi-arrow-left"></i>

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card h-100 border-0 shadow-sm rounded-4">

                    <div class="card-body">

                        <div class="d-flex align-items-center gap-3">

                            <div class="rounded-3 p-3 bg-warning-subtle text-warning-emphasis">
                                <i class="bi bi-clipboard-check fs-4"></i>
                            </div>

                            <div>

                                <div class="text-muted small">
                                    مجموع عملیات
                                </div>

                                <div class="fs-4 fw-bold">
                                    {{ $totalCount }}
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Operation links --}}
        <div class="card border-0 shadow-sm rounded-4">

            <div class="card-body p-3 p-md-4">

                <h2 class="h6 fw-bold mb-4">

                    <i class="bi bi-list-check me-1"></i>

                    عملیات نیازمند بررسی

                </h2>


                <div class="row g-3">


                    <div class="col-12 col-lg-4">

                        <a href="{{ route('admin.accounting.savings-transfers') }}"
                           class="text-decoration-none">

                            <div class="border rounded-4 p-3 h-100">

                                <div class="d-flex justify-content-between align-items-center">

                                    <div>

                                        <div class="fw-bold text-dark">
                                            واریز به حساب پس‌انداز
                                        </div>

                                        <div class="text-muted small mt-1">
                                            واریز خود و واریز به دیگر اعضا
                                        </div>

                                    </div>


                                    <div class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">

                                        {{ $savingsTransfersOwnCount + $savingsTransfersOtherCount }}

                                    </div>

                                </div>

                            </div>

                        </a>

                    </div>


                    <div class="col-12 col-lg-4">

                        <a href="{{ route('admin.accounting.withdrawals') }}"
                           class="text-decoration-none">

                            <div class="border rounded-4 p-3 h-100">

                                <div class="d-flex justify-content-between align-items-center">

                                    <div>

                                        <div class="fw-bold text-dark">
                                            برداشت از پس‌انداز
                                        </div>

                                        <div class="text-muted small mt-1">
                                            برداشت‌های پرداخت‌شده
                                        </div>

                                    </div>


                                    <div class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2">

                                        {{ $withdrawalsCount }}

                                    </div>

                                </div>

                            </div>

                        </a>

                    </div>


                    <div class="col-12 col-lg-4">

                        <a href="{{ route('admin.accounting.loan-payments') }}"
                           class="text-decoration-none">

                            <div class="border rounded-4 p-3 h-100">

                                <div class="d-flex justify-content-between align-items-center">

                                    <div>

                                        <div class="fw-bold text-dark">
                                            پرداخت اقساط
                                        </div>

                                        <div class="text-muted small mt-1">
                                            اقساط خود و اقساط دیگران
                                        </div>

                                    </div>


                                    <div class="badge bg-success-subtle text-success rounded-pill px-3 py-2">

                                        {{ $loanPaymentsOwnCount + $loanPaymentsOtherCount }}

                                    </div>

                                </div>

                            </div>

                        </a>

                    </div>


                </div>

            </div>

        </div>

    </div>

@endsection

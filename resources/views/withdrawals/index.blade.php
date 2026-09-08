@extends('layouts.app')

@section('title', 'درخواست‌های برداشت')

@section('content')

    <div class="container-fluid withdrawals-page">

        {{-- سربرگ صفحه --}}
        <div class="withdrawals-header">

            <div class="withdrawals-title-wrapper">

                <div class="withdrawals-title-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <div>
                    <h5 class="withdrawals-title">
                        درخواست‌های برداشت
                    </h5>

                    <p class="withdrawals-subtitle">
                        مدیریت و بررسی درخواست‌های برداشت مشتریان
                    </p>
                </div>

            </div>

        </div>


        {{-- پیام موفقیت --}}
        @if(session('success'))

            <div class="withdrawals-alert withdrawals-alert-success">

                <div class="withdrawals-alert-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>

                <div>
                    {{ session('success') }}
                </div>

            </div>

        @endif


        {{-- کارت اصلی --}}
        <div class="card withdrawals-card">

            <div class="withdrawals-card-header">

                <div class="withdrawals-card-title">

                    <span class="withdrawals-card-icon">
                        <i class="bi bi-list-ul"></i>
                    </span>

                    <div>

                        <h6>
                            فهرست درخواست‌ها
                        </h6>

                        <small>
                            درخواست‌های برداشت ثبت‌شده
                        </small>

                    </div>

                </div>

                @if($withdrawals->count())

                    <span class="withdrawals-count">
                        {{ $withdrawals->total() }}
                        درخواست
                    </span>

                @endif

            </div>


            <div class="card-body withdrawals-card-body">

                @if($withdrawals->count())

                    <div class="table-responsive withdrawals-table-wrapper">

                        <table class="table align-middle withdrawals-table">

                            <thead>

                            <tr>

                                <th>مشتری</th>

                                <th>شماره حساب</th>

                                <th>مبلغ</th>

                                <th>بانک مقصد</th>

                                <th>شماره شبا</th>

                                <th>وضعیت</th>

                                <th>تاریخ درخواست</th>

                                <th class="text-center">عملیات</th>

                            </tr>

                            </thead>


                            <tbody>

                            @foreach($withdrawals as $withdrawal)

                                <tr>

                                    {{-- مشتری --}}
                                    <td>

                                        <div class="withdrawal-customer">

                                            <div class="withdrawal-customer-icon">
                                                <i class="bi bi-person"></i>
                                            </div>

                                            <div class="withdrawal-customer-info">

                                                <strong>
                                                    {{ $withdrawal->account->customer->first_name }}
                                                    {{ $withdrawal->account->customer->last_name }}
                                                </strong>

                                                <small>
                                                    مشتری صندوق
                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- شماره حساب --}}
                                    <td>

                                        <span
                                            class="withdrawal-account-number"
                                            dir="ltr"
                                        >
                                            {{ $withdrawal->account->account_number }}
                                        </span>

                                    </td>


                                    {{-- مبلغ --}}
                                    <td>

                                        <div class="withdrawal-amount">

                                            <strong>
                                                {{ number_format($withdrawal->amount) }}
                                            </strong>

                                            <span>
                                                ریال
                                            </span>

                                        </div>

                                    </td>


                                    {{-- بانک مقصد --}}
                                    <td>

                                        <div class="withdrawal-bank">

                                            <span class="withdrawal-bank-icon">
                                                <i class="bi bi-bank"></i>
                                            </span>

                                            <span>
                                                {{ \App\Support\Iban::bankName($withdrawal->iban) }}
                                            </span>

                                        </div>

                                    </td>


                                    {{-- شماره شبا --}}
                                    <td>

                                        <span
                                            class="withdrawal-iban"
                                            dir="ltr"
                                        >
                                            {{ \App\Support\Iban::format($withdrawal->iban) }}
                                        </span>

                                    </td>


                                    {{-- وضعیت --}}
                                    <td>

                                        @if($withdrawal->status instanceof \App\Enums\WithdrawalStatus)

                                            <span
                                                class="withdrawal-status withdrawal-status-{{ $withdrawal->status->value }}"
                                            >

                                                <span class="withdrawal-status-dot"></span>

                                                {{ $withdrawal->status->label() }}

                                            </span>

                                        @else

                                            <span class="withdrawal-status withdrawal-status-unknown">

                                                <span class="withdrawal-status-dot"></span>

                                                نامشخص

                                            </span>

                                        @endif

                                    </td>


                                    {{-- تاریخ --}}
                                    <td>

                                        <div class="withdrawal-date">

                                            <span class="withdrawal-date-icon">
                                                <i class="bi bi-calendar3"></i>
                                            </span>

                                            <span>
                                                {{ \Morilog\Jalali\Jalalian::fromDateTime($withdrawal->created_at)->format('Y/m/d') }}
                                                <small>
                                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($withdrawal->created_at)->format('H:i') }}
                                                </small>
                                            </span>

                                        </div>

                                    </td>


                                    {{-- عملیات --}}
                                    <td class="text-center">

                                        <a
                                            href="{{ route('withdrawals.show', $withdrawal) }}"
                                            class="withdrawal-view-btn"
                                        >

                                            <i class="bi bi-eye"></i>

                                            <span>
                                                مشاهده
                                            </span>

                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{-- صفحه‌بندی --}}
                    @if($withdrawals->hasPages())

                        <div class="withdrawals-pagination">

                            {{ $withdrawals->links() }}

                        </div>

                    @endif


                @else

                    <div class="withdrawals-empty">

                        <div class="withdrawals-empty-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>

                        <h6>
                            درخواست برداشتی وجود ندارد
                        </h6>

                        <p>
                            هنوز هیچ درخواست برداشتی برای بررسی ثبت نشده است.
                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>

@endsection


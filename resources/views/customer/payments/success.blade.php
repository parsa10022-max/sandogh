@extends('customer.layouts.app')

@section('title', 'پرداخت موفق')

@section('header_title', 'پرداخت موفق')

@section('header_subtitle', 'پرداخت قسط با موفقیت انجام شد')

@section('content')

    <div class="customer-dashboard">

        <section class="customer-donations-section">

            <div class="customer-donations-title">
                <h2>
                    پرداخت موفق
                </h2>
            </div>

            <div class="customer-donation-success-card">

                <div class="customer-donation-success-icon">
                    <i class="bi bi-check-lg"></i>
                </div>

                <h3 class="customer-donation-success-title">
                    پرداخت قسط با موفقیت انجام شد
                </h3>

                <p class="customer-donation-success-message">
                    قسط شما با موفقیت پرداخت و ثبت شد.
                </p>

                <div class="customer-donation-success-info">

                    <div class="customer-donation-success-row">

                        <div class="customer-donation-success-label">
                        <span class="customer-donation-success-info-icon">
                            <i class="bi bi-cash-coin"></i>
                        </span>

                            شماره وام
                        </div>

                        <strong>
                            {{ $payment->loan->full_loan_number }}
                        </strong>

                    </div>

                    <div class="customer-donation-success-row">

                        <div class="customer-donation-success-label">
                        <span class="customer-donation-success-info-icon">
                            <i class="bi bi-list-check"></i>
                        </span>

                            شماره قسط
                        </div>

                        <strong>
                            {{ $payment->installment->installment_number }}
                        </strong>

                    </div>

                    <div class="customer-donation-success-row">

                        <div class="customer-donation-success-label">
                        <span class="customer-donation-success-info-icon">
                            <i class="bi bi-cash-stack"></i>
                        </span>

                            مبلغ پرداختی
                        </div>

                        <strong class="customer-donation-success-amount">
                            {{ number_format($payment->amount) }}
                            <small>ریال</small>
                        </strong>

                    </div>

                    <div class="customer-donation-success-row">

                        <div class="customer-donation-success-label">
                        <span class="customer-donation-success-info-icon">
                            <i class="bi bi-calendar3"></i>
                        </span>

                            تاریخ پرداخت
                        </div>

                        <strong>
                            {{ $payment->paid_at_jalali }}
                        </strong>

                    </div>

                    <div class="customer-donation-success-row">

                        <div class="customer-donation-success-label">
                        <span class="customer-donation-success-info-icon">
                            <i class="bi bi-receipt"></i>
                        </span>

                            کد رهگیری
                        </div>

                        <strong dir="ltr">
                            {{ $payment->tracking_code }}
                        </strong>

                    </div>

                </div>

                <div class="customer-donation-success-actions">

                    <a href="{{ route('customer.dashboard') }}"
                       class="customer-donation-success-dashboard">

                        <i class="bi bi-house"></i>

                        بازگشت به داشبورد

                    </a>

                </div>

            </div>

        </section>

    </div>

@endsection

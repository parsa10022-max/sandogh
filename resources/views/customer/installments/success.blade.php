@extends('customer.layouts.app')

@section('title', 'رسید پرداخت قسط')

@section('header_title', 'رسید پرداخت')

@section('header_subtitle', 'رسید موفق پرداخت قسط')

@section('content')

    <div class="container-fluid customer-other-installment-success">

        {{-- =====================================================
             HEADER
             ===================================================== --}}

        <div class="customer-other-installment-success-header">

            <div class="customer-other-installment-success-header-icon">

                <i class="bi bi-check-lg"></i>

            </div>

            <div class="customer-other-installment-success-header-content">

                <h2>
                    پرداخت با موفقیت انجام شد
                </h2>

                <p>
                    پرداخت قسط برای عضو صندوق با موفقیت ثبت شد.
                </p>

            </div>

        </div>


        {{-- =====================================================
             CARD
             ===================================================== --}}

        <div class="customer-other-installment-success-card">

            {{-- عنوان --}}

            <div class="customer-other-installment-success-card-title">

                <div class="customer-other-installment-success-card-title-icon">

                    <i class="bi bi-receipt"></i>

                </div>

                <span>
                اطلاعات پرداخت
            </span>

            </div>


            {{-- اطلاعات --}}

            <div class="customer-other-installment-success-info-grid">

                <div class="customer-other-installment-success-info-item">

                <span>
                    شماره وام
                </span>

                    <strong dir="ltr">
                        {{ $payment->loan->full_loan_number }}
                    </strong>

                </div>


                <div class="customer-other-installment-success-info-item">

                <span>
                    شماره قسط
                </span>

                    <strong>
                        {{ $payment->installment->installment_number }}
                    </strong>

                </div>


                <div class="customer-other-installment-success-info-item">

                <span>
                    نام صاحب وام
                </span>

                    <strong>
                        {{ $payment->loan->customer->full_name }}
                    </strong>

                </div>


                <div class="customer-other-installment-success-info-item">

                <span>
                    کد رهگیری
                </span>

                    <strong dir="ltr">
                        {{ $payment->tracking_code }}
                    </strong>

                </div>


                <div class="customer-other-installment-success-info-item">

                <span>
                    تاریخ پرداخت
                </span>

                    <strong>
                        {{ $payment->paid_at_jalali }}
                    </strong>

                </div>

            </div>


            {{-- =================================================
                 AMOUNT
                 ================================================= --}}

            <div class="customer-other-installment-success-amount">

                <div class="customer-other-installment-success-amount-icon">

                    <i class="bi bi-cash-coin"></i>

                </div>

                <div class="customer-other-installment-success-amount-content">

                <span>
                    مبلغ پرداخت
                </span>

                    <strong>
                        {{ number_format($payment->amount) }}

                        <small>
                            ریال
                        </small>
                    </strong>

                </div>

            </div>


            {{-- =================================================
                 SUCCESS NOTICE
                 ================================================= --}}

            <div class="customer-other-installment-success-notice">

                <div class="customer-other-installment-success-notice-icon">

                    <i class="bi bi-shield-check"></i>

                </div>

                <div class="customer-other-installment-success-notice-content">

                    <strong>
                        پرداخت با موفقیت ثبت شد
                    </strong>

                    <p>
                        مبلغ قسط با موفقیت پرداخت و تراکنش در سامانه ثبت شد.
                    </p>

                </div>

            </div>


            {{-- =================================================
                 ACTION BUTTONS
                 ================================================= --}}

            <div class="customer-other-installment-success-actions">

                <a
                    href="{{ route('payments.success', $payment) }}"
                    class="customer-other-installment-success-print"
                >

                    <i class="bi bi-receipt"></i>

                    <span>
                    مشاهده و چاپ رسید
                </span>

                </a>


                <a
                    href="{{ route('customer.installments.index') }}"
                    class="customer-other-installment-success-back"
                >

                    <i class="bi bi-arrow-right"></i>

                    <span>
                    بازگشت به اقساط
                </span>

                </a>


                <a
                    href="{{ route('customer.dashboard') }}"
                    class="customer-other-installment-success-home"
                >

                    <i class="bi bi-house"></i>

                    <span>
                    بازگشت به خانه
                </span>

                </a>

            </div>

        </div>

    </div>

@endsection

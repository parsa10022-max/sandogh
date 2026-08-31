@extends('customer.layouts.app')

@section('title', 'پرداخت قسط')

@section('header_title', 'پرداخت قسط دیگران')

@section('header_subtitle', 'پرداخت موفق قسط عضو دیگر صندوق')

@section('content')


    <div class="customer-other-installment-success">

        {{-- =========================================================
             Header
        ========================================================== --}}
        <div class="customer-other-installment-success-header">

            <div class="customer-other-installment-success-header-icon">
                <i class="bi bi-check-lg"></i>
            </div>

            <div class="customer-other-installment-success-header-content">

                <h2>
                    پرداخت با موفقیت انجام شد
                </h2>

                <p>
                    پرداخت قسط عضو دیگر صندوق با موفقیت ثبت شد.
                </p>

            </div>

        </div>


        {{-- =========================================================
             Payment Card
        ========================================================== --}}
        <div class="customer-other-installment-success-card">

            <div class="customer-other-installment-success-card-title">

            <span class="customer-other-installment-success-card-title-icon">
                <i class="bi bi-receipt"></i>
            </span>

                <span>
                اطلاعات پرداخت
            </span>

            </div>


            {{-- =====================================================
                 Information Grid
            ====================================================== --}}
            <div class="customer-other-installment-success-info-grid">

                {{-- نام عضو --}}
                <div class="customer-other-installment-success-info-item">

                <span>
                    نام عضو
                </span>

                    <strong>
                        {{ $payment->loan->customer->full_name }}
                    </strong>

                </div>


                {{-- شماره وام --}}
                <div class="customer-other-installment-success-info-item">

                <span>
                    شماره وام
                </span>

                    <strong dir="ltr">
                        {{ $payment->loan->loan_number }}
                    </strong>

                </div>


                {{-- شماره قسط --}}
                <div class="customer-other-installment-success-info-item">

                <span>
                    شماره قسط
                </span>

                    <strong>
                        {{ $payment->installment->installment_number }}
                    </strong>

                </div>


                {{-- شماره پیگیری --}}
                <div class="customer-other-installment-success-info-item">

                <span>
                    شماره پیگیری
                </span>

                    <strong dir="ltr">
                        {{ $payment->tracking_code ?? '---' }}
                    </strong>

                </div>

            </div>


            {{-- =====================================================
                 Amount
            ====================================================== --}}
            <div class="customer-other-installment-success-amount">

                <div class="customer-other-installment-success-amount-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>

                <div class="customer-other-installment-success-amount-content">

                <span>
                    مبلغ پرداختی
                </span>

                    <strong>
                        {{ number_format($payment->amount) }}

                        <small>
                            ریال
                        </small>
                    </strong>

                </div>

            </div>


            {{-- =====================================================
                 Success Notice
            ====================================================== --}}
            <div class="customer-other-installment-success-notice">

                <div class="customer-other-installment-success-notice-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>

                <div class="customer-other-installment-success-notice-content">

                    <strong>
                        پرداخت ثبت شد
                    </strong>

                    <p>
                        مبلغ قسط با موفقیت پرداخت شد و وضعیت قسط به
                        «پرداخت شده» تغییر کرد.
                    </p>

                </div>

            </div>


            {{-- =====================================================
                 Back Button
            ====================================================== --}}
            <a
                href="{{ route('customer.dashboard') }}"
                class="customer-other-installment-success-button">

            <span class="customer-other-installment-success-button-icon">
                <i class="bi bi-arrow-right"></i>
            </span>

                <span class="customer-other-installment-success-button-content">

                <strong>
                    بازگشت به خانه
                </strong>

                <small>
                    مشاهده اقساط وام
                </small>

            </span>

                <i class="bi bi-arrow-left customer-other-installment-success-button-arrow"></i>

            </a>

        </div>

    </div>


@endsection

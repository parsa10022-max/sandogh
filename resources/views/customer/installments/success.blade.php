@extends('customer.layouts.app')

@section('title', 'رسید پرداخت قسط')
@section('header_title', 'رسید پرداخت')
@section('header_subtitle', 'رسید موفق پرداخت قسط')

@section('content')

    <div class="container-fluid customer-payment-success">

        <div class="customer-payment-receipt">

            {{-- هدر رسید --}}
            <div class="customer-payment-receipt-header">

                <div class="customer-payment-success-icon">
                    <i class="bi bi-check-lg"></i>
                </div>

                <div>
                    <h5>پرداخت با موفقیت انجام شد</h5>
                    <span>رسید پرداخت قسط وام</span>
                </div>

            </div>


            {{-- مبلغ --}}
            <div class="customer-payment-amount">

                <span>مبلغ پرداخت</span>

                <strong>
                    {{ number_format($payment->amount) }}
                    <small>ریال</small>
                </strong>

            </div>


            {{-- اطلاعات --}}
            <div class="customer-payment-info">

                <div class="customer-payment-info-row">
                    <span>شماره وام</span>
                    <strong dir="ltr">
                        {{ $payment->loan->full_loan_number }}
                    </strong>
                </div>

                <div class="customer-payment-info-row">
                    <span>شماره قسط</span>
                    <strong>
                        {{ $payment->installment->installment_number }}
                    </strong>
                </div>

                <div class="customer-payment-info-row">
                    <span>تاریخ پرداخت</span>
                    <strong>
                        {{ $payment->paid_at_jalali }}
                    </strong>
                </div>

                <div class="customer-payment-info-row">
                    <span>کد رهگیری</span>
                    <strong dir="ltr">
                        {{ $payment->tracking_code }}
                    </strong>
                </div>

                @if($payment->bank_reference_number)

                    <div class="customer-payment-info-row">

                        <span>شماره مرجع بانک</span>

                        <strong dir="ltr">
                            {{ $payment->bank_reference_number }}
                        </strong>

                    </div>

                @endif

            </div>


            {{-- وضعیت --}}
            <div class="customer-payment-success-status">

                <i class="bi bi-shield-check"></i>

                پرداخت شما با موفقیت ثبت شد.

            </div>


            {{-- دکمه --}}
            {{-- دکمه‌ها --}}
            <div class="customer-payment-actions">

                <a
                    href="{{ route('customer.installments.index') }}"
                    class="customer-payment-back"
                >
                    <i class="bi bi-arrow-right"></i>
                    بازگشت به اقساط
                </a>

                <a
                    href="{{ route('customer.dashboard') }}"
                    class="customer-payment-home"
                >
                    <i class="bi bi-house"></i>
                    بازگشت به خانه
                </a>

            </div>

        </div>

    </div>

@endsection

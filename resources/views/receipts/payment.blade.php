@extends('receipts.layout')

@section('receipt-content')

    <div class="receipt-payment">

        <div class="receipt-payment-title">
            <i class="bi bi-receipt"></i>
            <span>اطلاعات پرداخت قسط</span>
        </div>

        <div class="receipt-payment-grid">

            <div class="receipt-payment-item">
                <span>نام مشتری</span>
                <strong>
                    {{ $payment->loan->customer->full_name }}
                </strong>
            </div>

            <div class="receipt-payment-item">
                <span>شماره وام</span>
                <strong dir="ltr">
                    {{ $payment->loan->full_loan_number }}
                </strong>
            </div>

            <div class="receipt-payment-item">
                <span>شماره قسط</span>
                <strong>
                    {{ $payment->installment->installment_number }}
                </strong>
            </div>

            <div class="receipt-payment-item receipt-payment-amount">
                <span>مبلغ پرداخت</span>
                <strong>
                    {{ number_format($payment->amount) }}
                    <small>ریال</small>
                </strong>
            </div>

            <div class="receipt-payment-item">
                <span>تاریخ پرداخت</span>
                <strong>
                    {{ $payment->paid_at_jalali }}
                </strong>
            </div>

            <div class="receipt-payment-item">
                <span>کد رهگیری</span>
                <strong dir="ltr">
                    {{ $payment->tracking_code }}
                </strong>
            </div>

            <div class="receipt-payment-item">
                <span>شماره مرجع بانک</span>
                <strong dir="ltr">
                    {{ $payment->bank_reference_number ?? '-' }}
                </strong>
            </div>

            <div class="receipt-payment-item">
                <span>شناسه تراکنش بانک</span>
                <strong dir="ltr">
                    {{ $payment->bank_transaction_id ?? '-' }}
                </strong>
            </div>

            <div class="receipt-payment-item">
                <span>درگاه پرداخت</span>
                <strong>
                    {{ $payment->gateway }}
                </strong>
            </div>

            <div class="receipt-payment-item">
                <span>اپراتور</span>
                <strong>
                    {{ $payment->user->name }}
                </strong>
            </div>

        </div>

    </div>

@endsection

@extends('receipts.layout')

@section('receipt-content')

    @php
        $paymentMethod = $withdrawal->transaction?->payment_method;
        $isBankTransfer = $paymentMethod === \App\Enums\PaymentMethod::BANK_TRANSFER;
    @endphp

    <div class="receipt-payment">

        <div class="receipt-payment-title">
            <i class="bi bi-wallet2"></i>
            <span>اطلاعات برداشت از حساب پس‌انداز</span>
        </div>

        <div class="receipt-payment-grid">

            {{-- شماره رسید --}}
            <div class="receipt-payment-item">
                <span>شماره رسید</span>
                <strong dir="ltr">
                    {{ $receipt_number ?? '-' }}
                </strong>
            </div>

            {{-- مبلغ برداشت --}}
            <div class="receipt-payment-item receipt-payment-amount">
                <span>مبلغ برداشت</span>
                <strong>
                    {{ fa_money($withdrawal->amount) }}
                    <small>ریال</small>
                </strong>
            </div>

            {{-- تاریخ برداشت --}}
            <div class="receipt-payment-item">
                <span>تاریخ پرداخت</span>
                <strong>
                    @if($withdrawal->paid_at)
                        {{ fa_number(
                            \Morilog\Jalali\Jalalian::fromDateTime(
                                $withdrawal->paid_at
                            )->format('Y/m/d H:i')
                        ) }}
                    @else
                        -
                    @endif
                </strong>
            </div>

            {{-- وضعیت برداشت --}}
            <div class="receipt-payment-item">
                <span>وضعیت برداشت</span>
                <strong>
                    <span class="text-success">
                        <i class="bi bi-check-circle-fill"></i>
                        پرداخت موفق
                    </span>
                </strong>
            </div>

            {{-- شماره حساب --}}
            <div class="receipt-payment-item">
                <span>شماره حساب</span>
                <strong dir="ltr">
                    {{ $withdrawal->account->account_number ?? '-' }}
                </strong>
            </div>

            {{-- روش پرداخت --}}
            <div class="receipt-payment-item">
                <span>روش پرداخت</span>
                <strong>
                    {{ $paymentMethod?->label() ?? '-' }}
                </strong>
            </div>

            {{-- بانک پرداخت‌کننده --}}
            @if($isBankTransfer)
                <div class="receipt-payment-item">
                    <span>بانک پرداخت‌کننده</span>
                    <strong>
                        {{ $withdrawal->payment_bank?->label() ?? '-' }}
                    </strong>
                </div>

                {{-- کد پیگیری بانکی --}}
                <div class="receipt-payment-item">
                    <span>کد پیگیری بانکی</span>
                    <strong dir="ltr">
                        {{ $withdrawal->payment_tracking_code ?? '-' }}
                    </strong>
                </div>
            @endif

            {{-- ثبت‌کننده پرداخت --}}
            <div class="receipt-payment-item">
                <span>ثبت‌کننده پرداخت</span>
                <strong>
                    {{ $withdrawal->paidBy?->username ?? '-' }}
                </strong>
            </div>

        </div>

    </div>

@endsection

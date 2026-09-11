@extends('receipts.layout')

@section('receipt-content')

    <div class="receipt-payment">

        <div class="receipt-payment-title">
            <i class="bi bi-arrow-left-right"></i>
            <span>اطلاعات انتقال از حساب پس‌انداز</span>
        </div>

        <div class="receipt-payment-grid">

            {{-- کد رهگیری صندوق --}}
            <div class="receipt-payment-item">

                <span>
                    کد رهگیری پرداخت
                </span>

                <strong dir="ltr">
                    {{ $receipt_number ?? $transfer->tracking_code ?? '-' }}
                </strong>

            </div>


            {{-- مبلغ انتقال --}}
            <div class="receipt-payment-item receipt-payment-amount">

                <span>
                    مبلغ انتقال
                </span>

                <strong>

                    {{ number_format($transfer->amount) }}

                    <small>
                        ریال
                    </small>

                </strong>

            </div>


            {{-- تاریخ انتقال --}}
            <div class="receipt-payment-item">

                <span>
                    تاریخ انتقال
                </span>

                <strong>

                    @if($transfer->paid_at)

                        {{ \Morilog\Jalali\Jalalian::fromDateTime(
                            $transfer->paid_at
                        )->format('Y/m/d H:i') }}

                    @else

                        -

                    @endif

                </strong>

            </div>


            {{-- وضعیت انتقال --}}
            <div class="receipt-payment-item">

                <span>
                    وضعیت انتقال
                </span>

                <strong>

                    <span class="text-success">

                        <i class="bi bi-check-circle-fill"></i>

                        انتقال موفق

                    </span>

                </strong>

            </div>


            {{-- حساب مبدا --}}
            <div class="receipt-payment-item">

                <span>
                    حساب پس‌انداز مبدا
                </span>

                <strong dir="ltr">

                    {{ $transfer->account?->account_number ?? '-' }}

                </strong>

            </div>


            {{-- انتقال‌دهنده --}}
            <div class="receipt-payment-item">

                <span>
                    انتقال‌دهنده
                </span>

                <strong>

                    @if($transfer->sender)

                        {{ $transfer->sender->first_name }}
                        {{ $transfer->sender->last_name }}

                    @else

                        -

                    @endif

                </strong>

            </div>


            {{-- حساب مقصد --}}
            <div class="receipt-payment-item">

                <span>
                    حساب پس‌انداز مقصد
                </span>

                <strong dir="ltr">

                    {{ $transfer->receiver?->accounts?->first()?->account_number ?? '-' }}

                </strong>

            </div>


            {{-- دریافت‌کننده --}}
            <div class="receipt-payment-item">

                <span>
                    دریافت‌کننده
                </span>

                <strong>

                    @if($transfer->receiver)

                        {{ $transfer->receiver->first_name }}
                        {{ $transfer->receiver->last_name }}

                    @else

                        -

                    @endif

                </strong>

            </div>

        </div>





    </div>

@endsection

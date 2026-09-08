@extends('layouts.app')

@section('title', 'پرداخت موفق کمک')



@section('content')


    <div class="donation-success-page">

        <div class="donation-success-card">

            {{-- Success Icon --}}
            <div class="donation-success-icon">

                <i class="bi bi-check-lg"></i>

            </div>


            {{-- Title --}}
            <h1 class="donation-success-title">
                پرداخت کمک با موفقیت انجام شد
            </h1>

            <p class="donation-success-subtitle">
                از حمایت و کمک شما به صندوق سپاسگزاریم.
            </p>


            {{-- Payment Details --}}
            <div class="donation-success-details">

                <div class="donation-success-detail">

                    <div class="donation-success-detail__icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                    <div class="donation-success-detail__content">

                    <span class="donation-success-detail__label">
                        مبلغ پرداختی
                    </span>

                        <strong class="donation-success-detail__value">

                            {{ number_format($donationPayment->amount) }}

                            <small>
                                ریال
                            </small>

                        </strong>

                    </div>

                </div>


                <div class="donation-success-detail">

                    <div class="donation-success-detail__icon">
                        <i class="bi bi-upc-scan"></i>
                    </div>

                    <div class="donation-success-detail__content">

                    <span class="donation-success-detail__label">
                        کد پیگیری
                    </span>

                        <strong
                            class="donation-success-detail__value
                               donation-success-detail__value--tracking"
                            dir="ltr"
                        >
                            {{ $donationPayment->tracking_code }}
                        </strong>

                    </div>

                </div>

            </div>


            {{-- Actions --}}
            <div class="donation-success-actions">

                <a href="{{ route('donation.create') }}"
                   class="donation-success-btn donation-success-btn--primary">

                    <i class="bi bi-heart-fill"></i>

                    کمک مجدد

                </a>

                <a href="{{ url('/') }}"
                   class="donation-success-btn donation-success-btn--secondary">

                    <i class="bi bi-house"></i>

                    بازگشت به صفحه اصلی

                </a>

            </div>

        </div>

    </div>

@endsection

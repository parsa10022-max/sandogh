@extends('customer.layouts.app')

@section('title', 'پرداخت کمک')

@section('header_title', 'پرداخت کمک')

@section('header_subtitle', 'انتقال به درگاه پرداخت')

@section('content')

    @php
        $accountName = $donationPayment->account->name ?? '';

        if (str_contains($accountName, 'صندوق')) {
            $accountColor = '#e05268';
        } elseif (str_contains($accountName, 'مسجد')) {
            $accountColor = '#4778dc';
        } elseif (
            str_contains($accountName, 'باقی') ||
            str_contains($accountName, 'الصالحات')
        ) {
            $accountColor = '#3d9b5c';
        } else {
            $accountColor = '#6040e8';
        }
    @endphp

    <div class="customer-dashboard">

        <section class="customer-donations-section">

            <div class="customer-donations-title">
                <h2>
                    پرداخت کمک
                </h2>
            </div>


            <div class="customer-donation-payment-card">

                {{-- Header --}}
                <div class="customer-donation-payment-header">

                <span class="customer-donation-payment-icon"
                      style="
                          background: {{ $accountColor }}1A;
                          color: {{ $accountColor }};
                          ">

                    <i class="bi bi-heart-fill"></i>

                </span>

                    <div>

                        <h3>
                            کمک به {{ $accountName }}
                        </h3>

                        <span>
                        بررسی اطلاعات پرداخت
                    </span>

                    </div>

                </div>


                {{-- اطلاعات --}}
                <div class="customer-donation-payment-info">

                    <div class="customer-donation-payment-row">

                    <span>
                        حساب مقصد
                    </span>

                        <strong>
                            {{ $accountName }}
                        </strong>

                    </div>


                    <div class="customer-donation-payment-row">

                    <span>
                        شماره حساب
                    </span>

                        <strong dir="ltr">
                            {{ $donationPayment->account->account_number }}
                        </strong>

                    </div>


                    <div class="customer-donation-payment-row amount">

                    <span>
                        مبلغ کمک
                    </span>

                        <strong>
                            {{ number_format($donationPayment->amount) }}
                            <small>ریال</small>
                        </strong>

                    </div>

                </div>


                {{-- اطلاع --}}
                <div class="customer-donation-payment-alert">

                    <i class="bi bi-shield-check"></i>

                    <span>
                    برای تکمیل کمک، به درگاه پرداخت منتقل خواهید شد.
                </span>

                </div>


                {{-- پرداخت --}}
                <form method="POST"
                      action="{{ route('customer.donations.pay', $donationPayment) }}">

                    @csrf

                    <button type="submit"
                            class="customer-donation-submit">

                    <span>
                        <i class="bi bi-credit-card"></i>
                        پرداخت از طریق درگاه
                    </span>

                        <i class="bi bi-arrow-left"></i>

                    </button>

                </form>


                {{-- انصراف --}}
                <a href="{{ route('customer.dashboard') }}"
                   class="customer-donation-payment-cancel">

                    <i class="bi bi-arrow-right"></i>

                    انصراف و بازگشت به داشبورد

                </a>

            </div>

        </section>

    </div>

@endsection

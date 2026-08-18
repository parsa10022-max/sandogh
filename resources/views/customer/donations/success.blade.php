@extends('customer.layouts.app')

@section('title', 'پرداخت موفق')

@section('header_title', 'پرداخت موفق')

@section('header_subtitle', 'کمک شما با موفقیت ثبت شد')

@section('content')

    <div class="customer-dashboard">

        <section class="customer-donations-section">

            {{-- عنوان صفحه --}}
            <div class="customer-donations-title">
                <h2>
                    پرداخت موفق
                </h2>
            </div>


            {{-- کارت موفقیت --}}
            <div class="customer-donation-success-card">


                {{-- آیکون موفقیت --}}
                <div class="customer-donation-success-icon">

                    <i class="bi bi-check-lg"></i>

                </div>


                {{-- عنوان --}}
                <h3 class="customer-donation-success-title">

                    پرداخت با موفقیت انجام شد

                </h3>


                <p class="customer-donation-success-message">

                    کمک شما با موفقیت ثبت و پرداخت شد.

                </p>


                {{-- اطلاعات پرداخت --}}
                <div class="customer-donation-success-info">


                    {{-- حساب مقصد --}}
                    <div class="customer-donation-success-row">

                        <div class="customer-donation-success-label">

                        <span class="customer-donation-success-info-icon">
                            <i class="bi bi-heart-fill"></i>
                        </span>

                            حساب مقصد

                        </div>

                        <strong>
                            {{ $donationPayment->account->name }}
                        </strong>

                    </div>


                    {{-- شماره حساب --}}
                    <div class="customer-donation-success-row">

                        <div class="customer-donation-success-label">

                        <span class="customer-donation-success-info-icon">
                            <i class="bi bi-bank"></i>
                        </span>

                            شماره حساب

                        </div>

                        <strong dir="ltr">
                            {{ $donationPayment->account->account_number }}
                        </strong>

                    </div>


                    {{-- مبلغ --}}
                    <div class="customer-donation-success-row">

                        <div class="customer-donation-success-label">

                        <span class="customer-donation-success-info-icon">
                            <i class="bi bi-cash-stack"></i>
                        </span>

                            مبلغ پرداختی

                        </div>

                        <strong class="customer-donation-success-amount">

                            {{ number_format($donationPayment->amount) }}

                            <small>ریال</small>

                        </strong>

                    </div>


                    {{-- کد رهگیری --}}
                    <div class="customer-donation-success-row">

                        <div class="customer-donation-success-label">

                        <span class="customer-donation-success-info-icon">
                            <i class="bi bi-receipt"></i>
                        </span>

                            کد رهگیری

                        </div>

                        <strong dir="ltr">

                            {{ $donationPayment->tracking_code }}

                        </strong>

                    </div>


                </div>


                {{-- دکمه‌ها --}}
                <div class="customer-donation-success-actions">


                    <a href="{{ route('customer.dashboard') }}"
                       class="customer-donation-success-dashboard">

                        <i class="bi bi-house"></i>

                        بازگشت به داشبورد

                    </a>


                    <a href="{{ route('customer.donations.create') }}"
                       class="customer-donation-success-again">

                        <i class="bi bi-heart"></i>

                        ثبت کمک جدید

                    </a>


                </div>


            </div>

        </section>

    </div>

@endsection

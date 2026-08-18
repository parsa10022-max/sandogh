
@extends('customer.layouts.app')

@section('title', 'اقساط وام من')
@section('header_title', 'اقساط وام من')
@section('header_subtitle', 'مشاهده و پرداخت اقساط وام')

@section('content')

    <div class="container-fluid customer-installments-page">

        @if(!$loan)

            {{-- =====================================================
                 حالت بدون وام
            ====================================================== --}}

            <div class="customer-installments-empty-card">

                <div class="customer-installments-empty-icon">
                    <i class="bi bi-journal-x"></i>
                </div>

                <h5>وام فعالی ندارید</h5>

                <p>
                    در حال حاضر وام قابل پرداختی برای شما ثبت نشده است.
                </p>

            </div>

        @else

            {{-- =====================================================
                 خلاصه وام
            ====================================================== --}}

            <section class="customer-installments-summary">

                <div class="customer-installments-section-title">

                    <div class="customer-installments-section-icon">
                        <i class="bi bi-credit-card-2-front"></i>
                    </div>

                    <div>
                        <h2>اطلاعات وام</h2>
                        <span>خلاصه وضعیت وام شما</span>
                    </div>

                </div>


                <div class="customer-installments-summary-grid">

                    {{-- شماره وام --}}
                    <div class="customer-installments-summary-item">

                        <span>شماره وام</span>

                        <strong dir="ltr">
                            {{ $loan->full_loan_number }}
                        </strong>

                    </div>


                    {{-- نوع وام --}}
                    <div class="customer-installments-summary-item">

                        <span>نوع وام</span>

                        <strong>
                            {{ $loan->loanType->name }}
                        </strong>

                    </div>


                    {{-- مبلغ وام --}}
                    <div class="customer-installments-summary-item">

                        <span>مبلغ وام</span>

                        <strong>
                            {{ number_format($loan->loan_amount) }}
                            ریال
                        </strong>

                    </div>


                    {{-- مبلغ هر قسط --}}
                    <div class="customer-installments-summary-item">

                        <span>مبلغ هر قسط</span>

                        <strong>
                            {{ number_format($loan->installment_amount) }}
                            ریال
                        </strong>

                    </div>

                </div>

            </section>


            {{-- =====================================================
                 برنامه اقساط
            ====================================================== --}}

            <section class="customer-installments-section">

                {{-- عنوان بخش --}}
                <div class="customer-installments-section-title">

                    <div class="customer-installments-section-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>

                    <div>
                        <h2>برنامه اقساط</h2>
                        <span>وضعیت و پرداخت اقساط وام</span>
                    </div>

                </div>


                {{-- =================================================
                     اقساط

                     ساختار ردیف‌ها حفظ شده است.
                     CSS تعداد ستون‌ها را کنترل می‌کند:

                     موبایل  → ۲ ستون
                     تبلت    → ۳ ستون
                     دسکتاپ  → ۵ ستون
                ================================================== --}}

                <div class="customer-installments-columns">

                    <div class="customer-installments-row">

                        @foreach($loan->installments as $item)

                            @php

                                $isPaid =
                                    $item->status === \App\Enums\InstallmentStatus::PAID;

                                $isPayable =
                                    $installment
                                    && $item->id === $installment->id;

                            @endphp


                            <article
                                class="customer-installment-card
                                {{ $isPaid ? 'is-paid' : '' }}
                                {{ $isPayable ? 'is-payable' : '' }}"
                            >

                                {{-- =================================================
                                     ردیف بالای کارت
                                     شماره + عنوان + تاریخ سررسید
                                ================================================== --}}

                                <div class="customer-installment-header">

                                    <div class="customer-installment-heading">

                                        <div class="customer-installment-number">
                                            {{ $item->installment_number }}
                                        </div>

                                        <div class="customer-installment-title">
                                            قسط {{ $item->installment_number }}
                                        </div>

                                    </div>


                                    <div class="customer-installment-due-date">

                                        <i class="bi bi-calendar3"></i>

                                        <span>
                                            {{ $item->due_date_jalali }}
                                        </span>

                                    </div>

                                </div>


                                {{-- =================================================
                                     ردیف پایین کارت
                                     مبلغ + وضعیت / عملیات
                                ================================================== --}}

                                <div class="customer-installment-bottom">

                                    <div class="customer-installment-amount">

                                        <strong>
                                            {{ number_format($item->amount) }}
                                            <small>ریال</small>
                                        </strong>

                                    </div>


                                    <div class="customer-installment-action">

                                        {{-- پرداخت شده --}}
                                        @if($isPaid)

                                            @if($item->payment)

                                                <a
                                                    href="{{ route('customer.installments.payment.success', $item->payment) }}"
                                                    class="customer-installment-receipt"
                                                >

                                                    <i class="bi bi-receipt"></i>

                                                    رسید

                                                </a>

                                            @else

                                                <span class="customer-installment-status paid">

                                                    <i class="bi bi-check-circle"></i>

                                                    پرداخت شده

                                                </span>

                                            @endif


                                            {{-- قسط قابل پرداخت --}}
                                        @elseif($isPayable)

                                            <form
                                                method="POST"
                                                action="{{ route('payments.pay', $item) }}"
                                                class="customer-installment-form"
                                            >

                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="customer-installment-pay"
                                                >

                                                    <i class="bi bi-credit-card"></i>

                                                    پرداخت

                                                </button>

                                            </form>


                                            {{-- منتظر قسط قبلی --}}
                                        @else

                                            <span class="customer-installment-status waiting">

                                                <i class="bi bi-lock"></i>

                                                منتظر قسط قبلی

                                            </span>

                                        @endif

                                    </div>

                                </div>


                                {{-- =================================================
                                     تاریخ پرداخت
                                     فقط برای اقساط پرداخت‌شده
                                ================================================== --}}

                                @if($item->paid_at)

                                    <div class="customer-installment-paid-date">

                                        <i class="bi bi-check2-circle"></i>

                                        پرداخت در
                                        {{ $item->paid_at_jalali }}

                                    </div>

                                @endif

                            </article>

                        @endforeach

                    </div>

                </div>


                {{-- =====================================================
                     فوتر
                ====================================================== --}}

                <div class="customer-installments-footer">

                    <span>

                        <i class="bi bi-list-check"></i>

                        تعداد اقساط:

                        <strong>
                            {{ $loan->installments->count() }}
                        </strong>

                    </span>


                    <span>

                        مجموع:

                        <strong>
                            {{ number_format($loan->installments->sum('amount')) }}
                            ریال
                        </strong>

                    </span>

                </div>

            </section>

        @endif

    </div>

@endsection


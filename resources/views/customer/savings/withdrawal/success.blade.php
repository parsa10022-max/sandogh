@extends('customer.layouts.app')

@section('title', 'برداشت موفق')

@section('header_title', 'برداشت موفق')

@section('header_subtitle', 'درخواست برداشت با موفقیت ثبت شد')

@section('content')

    <div class="customer-savings-withdrawal-success-page">

        <div class="customer-savings-withdrawal-success-card">


            {{-- =====================================================
                 HEADER
            ====================================================== --}}

            <div class="customer-savings-withdrawal-success-header">

                <div class="customer-savings-withdrawal-success-icon">

                    <i class="bi bi-check-lg"></i>

                </div>

                <div class="customer-savings-withdrawal-success-header-content">

                    <h1>
                        برداشت موفق
                    </h1>

                    <span>
                        درخواست برداشت شما با موفقیت ثبت شد
                    </span>

                </div>

            </div>


            {{-- =====================================================
                 SUCCESS MESSAGE
            ====================================================== --}}

            <div class="customer-savings-withdrawal-success-message">

                <i class="bi bi-check-circle-fill"></i>

                <div>

                    <strong>
                        درخواست برداشت با موفقیت ثبت شد.
                    </strong>

                    <span>
                        درخواست شما ثبت شده و پس از بررسی پرداخت خواهد شد.
                    </span>

                </div>

            </div>


            {{-- =====================================================
                 IBAN INFORMATION
            ====================================================== --}}

            @php

                $iban = strtoupper(
                    trim($customer->iban ?? '')
                );

                // حذف فاصله‌های قبلی
                $iban = preg_replace(
                    '/\s+/',
                    '',
                    $iban
                );

                // نمایش چهار رقم چهار رقم
                $ibanFormatted = trim(
                    chunk_split(
                        $iban,
                        4,
                        ' '
                    )
                );

            @endphp


            <div class="customer-savings-withdrawal-iban-alert">

                <i class="bi bi-bank"></i>

                <div class="customer-savings-withdrawal-iban-content">


                    {{-- عنوان --}}

                    <span class="customer-savings-withdrawal-iban-title">

                        حساب مقصد برداشت

                    </span>


                    {{-- شماره شبا --}}

                    @if($iban)

                        <strong
                            dir="ltr"
                            class="customer-savings-withdrawal-iban-number"
                        >
                            {{ $ibanFormatted }}
                        </strong>

                    @else

                        <strong
                            class="customer-savings-withdrawal-iban-empty"
                        >
                            شماره شبا ثبت نشده است
                        </strong>

                    @endif


                    {{-- نام صاحب حساب --}}

                    <div class="customer-savings-withdrawal-iban-name">

                        <i class="bi bi-person-fill"></i>

                        <span>
                            به نام:
                        </span>

                        <strong>
                            {{ $customer->full_name }}
                        </strong>

                    </div>


                    {{-- هشدار --}}

                    <small class="customer-savings-withdrawal-iban-owner">

                        مبلغ برداشت فقط به شماره شبای ثبت‌شده
                        و به نام خود مشتری واریز می‌شود.

                    </small>

                </div>

            </div>


            {{-- =====================================================
                 INFORMATION
            ====================================================== --}}

            <div class="customer-savings-withdrawal-success-info">


                {{-- مبلغ برداشت --}}

                <div class="customer-savings-withdrawal-success-info-item">

                    <span>
                        مبلغ برداشت
                    </span>

                    <strong class="customer-savings-withdrawal-success-amount">

                        {{ number_format($withdrawal->amount) }}

                        <small>
                            ریال
                        </small>

                    </strong>

                </div>


                {{-- وضعیت --}}

                <div class="customer-savings-withdrawal-success-info-item">

                    <span>
                        وضعیت
                    </span>


                    @if(
                        $withdrawal->status instanceof
                        \App\Enums\WithdrawalStatus
                    )

                        <span
                            class="
                                customer-savings-withdrawal-status-badge
                                customer-savings-withdrawal-status-{{ $withdrawal->status->badge() }}
                                "
                        >

                            {{ $withdrawal->status->label() }}

                        </span>

                    @else

                        <span
                            class="customer-savings-withdrawal-status-badge"
                        >

                            {{ $withdrawal->status }}

                        </span>

                    @endif

                </div>


                {{-- تاریخ درخواست --}}

                <div class="customer-savings-withdrawal-success-info-item">

                    <span>
                        تاریخ درخواست
                    </span>

                    <strong dir="ltr">

                        {{
                            \Morilog\Jalali\Jalalian::fromDateTime(
                                $withdrawal->created_at
                            )->format('Y/m/d H:i')
                        }}

                    </strong>

                </div>

            </div>


            {{-- =====================================================
                 NOTE
            ====================================================== --}}

            <div class="customer-savings-withdrawal-success-note">

                <i class="bi bi-shield-check"></i>

                <span>
                    درخواست برداشت در سوابق حساب شما ثبت شد.
                </span>

            </div>


            {{-- =====================================================
                 ACTION
            ====================================================== --}}

            <div class="customer-savings-withdrawal-success-actions">

                <a
                    href="{{ route('customer.savings.transactions') }}"
                    class="customer-savings-withdrawal-success-submit"
                >

                    <i class="bi bi-receipt"></i>

                    <span>
                        مشاهده گردش حساب
                    </span>

                    <i class="bi bi-arrow-left"></i>

                </a>

            </div>


        </div>

    </div>

@endsection

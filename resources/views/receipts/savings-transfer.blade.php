@extends('customer.layouts.app')

@section('title', 'واریز موفق')

@section('header_title', 'واریز موفق')

@section('header_subtitle', 'واریز به حساب پس‌انداز با موفقیت انجام شد')

@push('styles')
    @vite('resources/css/customer/savings-transfer-success.css')
@endpush

@section('content')

    <div class="customer-savings-success-page">

        <div class="customer-savings-success-card">


            {{-- =====================================================
                 SUCCESS HEADER
            ====================================================== --}}

            <div class="customer-savings-success-header">

                <div class="customer-savings-success-header-icon">

                    <i class="bi bi-check-lg"></i>

                </div>


                <div class="customer-savings-success-header-content">

                    <h1>
                        واریز موفق
                    </h1>

                    <span>
                    واریز به حساب پس‌انداز با موفقیت انجام شد
                </span>

                </div>

            </div>


            {{-- =====================================================
                 SUCCESS MESSAGE
            ====================================================== --}}

            <div class="customer-savings-success-message">

                <i class="bi bi-check-circle-fill"></i>

                <div>

                    <strong>
                        واریز با موفقیت انجام شد.
                    </strong>

                    <span>
                    مبلغ با موفقیت به حساب پس‌انداز شما اضافه شد.
                </span>

                </div>

            </div>


            {{-- =====================================================
                 PAYMENT INFORMATION
            ====================================================== --}}

            <div class="customer-savings-success-info">


                {{-- -------------------------------------------------
                     کد رهگیری صندوق
                -------------------------------------------------- --}}

                <div class="customer-savings-success-info-item">

                <span class="customer-savings-success-label">

                    <i class="bi bi-receipt"></i>

                    کد رهگیری صندوق

                </span>

                    <strong
                        dir="ltr"
                        class="customer-savings-success-tracking"
                    >
                        {{ $transfer->tracking_code }}
                    </strong>

                </div>


                {{-- -------------------------------------------------
                     مبلغ واریز
                -------------------------------------------------- --}}

                <div class="customer-savings-success-info-item">

                <span class="customer-savings-success-label">

                    <i class="bi bi-cash-stack"></i>

                    مبلغ واریز

                </span>

                    <strong class="customer-savings-success-amount">

                        {{ number_format($transfer->amount) }}

                        <small>
                            ریال
                        </small>

                    </strong>

                </div>


                {{-- -------------------------------------------------
                     تاریخ پرداخت
                -------------------------------------------------- --}}

                <div class="customer-savings-success-info-item">

                <span class="customer-savings-success-label">

                    <i class="bi bi-calendar3"></i>

                    تاریخ پرداخت

                </span>

                    <strong dir="ltr">

                        @if($transfer->paid_at)

                            {{ \Morilog\Jalali\Jalalian::fromDateTime($transfer->paid_at)->format('Y/m/d H:i') }}

                        @else

                            -

                        @endif

                    </strong>

                </div>


                {{-- -------------------------------------------------
                     شناسه تراکنش بانکی
                -------------------------------------------------- --}}

                <div class="customer-savings-success-info-item">

                <span class="customer-savings-success-label">

                    <i class="bi bi-credit-card-2-front"></i>

                    شناسه تراکنش بانکی

                </span>

                    <strong dir="ltr">

                        {{ $transfer->bank_transaction_id ?? '-' }}

                    </strong>

                </div>


                {{-- -------------------------------------------------
                     شماره مرجع بانکی
                -------------------------------------------------- --}}

                <div class="customer-savings-success-info-item">

                <span class="customer-savings-success-label">

                    <i class="bi bi-upc-scan"></i>

                    شماره مرجع بانکی

                </span>

                    <strong dir="ltr">

                        {{ $transfer->bank_reference_number ?? '-' }}

                    </strong>

                </div>


                {{-- -------------------------------------------------
                     وضعیت
                -------------------------------------------------- --}}

                <div class="customer-savings-success-info-item">

                <span class="customer-savings-success-label">

                    <i class="bi bi-info-circle"></i>

                    وضعیت پرداخت

                </span>


                    @if($transfer->status === 'paid')

                        <strong class="customer-savings-success-status paid">

                            <i class="bi bi-check-circle-fill"></i>

                            پرداخت موفق

                        </strong>

                    @elseif($transfer->status === 'pending')

                        <strong class="customer-savings-success-status pending">

                            <i class="bi bi-clock-fill"></i>

                            در انتظار پرداخت

                        </strong>

                    @else

                        <strong class="customer-savings-success-status failed">

                            <i class="bi bi-x-circle-fill"></i>

                            پرداخت ناموفق

                        </strong>

                    @endif

                </div>


            </div>


            {{-- =====================================================
                 SECURITY NOTE
            ====================================================== --}}

            <div class="customer-savings-success-note">

                <i class="bi bi-shield-check"></i>

                <span>
                این پرداخت با موفقیت ثبت و در سوابق حساب شما ذخیره شد.
            </span>

            </div>


            {{-- =====================================================
                 ACTION
            ====================================================== --}}

            <div class="customer-savings-success-actions">

                <a
                    href="{{ route('customer.dashboard') }}"
                    class="customer-savings-success-submit"
                >

                    <i class="bi bi-house"></i>

                    <span>
                    بازگشت به پنل
                </span>

                    <i class="bi bi-arrow-left"></i>

                </a>

            </div>


        </div>

    </div>


@endsection

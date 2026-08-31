@extends('customer.layouts.app')

@section('title', 'پرداخت قسط دیگران')
@section('header_title', 'پرداخت قسط')
@section('header_subtitle', 'پرداخت قسط عضو دیگر')

@section('content')

    <div class="customer-other-installment">

        {{-- =========================================================
             Header
        ========================================================== --}}
        <div class="customer-other-installment-header">

            <div class="customer-other-installment-header-icon">
                <i class="bi bi-people-fill"></i>
            </div>

            <div class="customer-other-installment-header-content">

                <h2>
                    پرداخت قسط دیگران
                </h2>

                <p>
                    اطلاعات قسط را بررسی کرده و سپس پرداخت را انجام دهید.
                </p>

            </div>

        </div>


        {{-- =========================================================
             Installment Information Card
        ========================================================== --}}
        <div class="customer-other-installment-card">

            <div class="customer-other-installment-card-title">

            <span class="customer-other-installment-card-title-icon">
                <i class="bi bi-file-earmark-text"></i>
            </span>

                <span>
                اطلاعات قسط
            </span>

            </div>


            {{-- =====================================================
                 Compact Information Grid
            ====================================================== --}}
            <div class="customer-other-installment-info-grid">

                <div class="customer-other-installment-info-item">
        <span class="customer-other-installment-info-label">
            نام عضو
        </span>

                    <strong>
                        {{ $installment->loan->customer->full_name }}
                    </strong>
                </div>


                <div class="customer-other-installment-info-item">
        <span class="customer-other-installment-info-label">
            شماره وام
        </span>

                    <strong dir="ltr">
                        {{ $installment->loan->full_loan_number }}
                    </strong>
                </div>


                <div class="customer-other-installment-info-item">
        <span class="customer-other-installment-info-label">
            نوع وام
        </span>

                    <strong>
                        {{ $installment->loan->loanType->name }}
                    </strong>
                </div>


                <div class="customer-other-installment-info-item">
        <span class="customer-other-installment-info-label">
            شماره قسط
        </span>

                    <strong>
                        {{ $installment->installment_number }}
                    </strong>
                </div>

            </div>


            {{-- =====================================================
                 Amount
            ====================================================== --}}



            {{-- =====================================================
                 Notice
            ====================================================== --}}
            <div class="customer-other-installment-notice">

                <div class="customer-other-installment-notice-icon">
                    <i class="bi bi-info-circle-fill"></i>
                </div>

                <div class="customer-other-installment-notice-content">

                    <strong>
                        توجه
                    </strong>

                    <p>
                        این قسط متعلق به عضو دیگری است.
                        پس از تأیید، مبلغ قسط از طریق درگاه پرداخت خواهد شد.
                    </p>

                </div>

            </div>


            {{-- =====================================================
                 Payment
            ====================================================== --}}
            <form method="POST"
                  action="{{ route('customer.installments.others.pay') }}">

                @csrf

                <input type="hidden"
                       name="installment_id"
                       value="{{ $installment->id }}">

                <button type="submit"
                        class="customer-other-installment-pay-button">

        <span class="customer-other-installment-pay-icon">
            <i class="bi bi-credit-card-fill"></i>
        </span>

                    <span class="customer-other-installment-pay-content">

            <strong>
                پرداخت قسط
            </strong>

            <span class="customer-other-installment-pay-amount">
                {{ number_format($installment->amount) }}
                <small>ریال</small>
            </span>

        </span>

                    <i class="bi bi-arrow-left customer-other-installment-pay-arrow"></i>

                </button>

            </form>

        </div>

    </div>

@endsection

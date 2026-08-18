@extends('customer.layouts.app')

@section('title', 'کمک به صندوق')

@section('header_title', 'کمک به صندوق')

@section('header_subtitle', 'حمایت مالی از صندوق')

@section('content')

    <div class="customer-dashboard">

        {{-- =====================================================
             HEADER
             ===================================================== --}}
        <section class="customer-donations-section">

            <div class="customer-donations-title">
                <h2>
                    کمک به صندوق
                </h2>
            </div>


            {{-- =================================================
                 DONATION CARD
                 ================================================= --}}
            <div class="customer-donation-form-card">

                @php
                    $accountName = $selectedAccount->name ?? '';

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

                <div class="customer-donation-form-header">

                    <h5 class="mb-0">

                        <span class="customer-donation-form-icon"
                              style="
                                  background: {{ $accountColor }}1A;
                                  color: {{ $accountColor }};
                                  ">

                              <i class="bi bi-heart-fill"></i>

                     </span>
                            حساب مقصد
                         {{ $accountName }}

                    </h5>

                </div>


                {{-- پیام موفقیت --}}
                @if(session('success'))

                    <div class="customer-donation-alert customer-donation-alert-success">

                        <i class="bi bi-check-circle-fill"></i>

                        <span>
                            {{ session('success') }}
                        </span>

                    </div>

                @endif


                {{-- خطاها --}}
                @if($errors->any())

                    <div class="customer-donation-alert customer-donation-alert-danger">

                        <i class="bi bi-exclamation-circle-fill"></i>

                        <div>

                            @foreach($errors->all() as $error)

                                <div>
                                    {{ $error }}
                                </div>

                            @endforeach

                        </div>

                    </div>

                @endif


                <form method="POST"
                      action="{{ route('customer.donations.store') }}">

                    @csrf

                    <input type="hidden"
                           name="account_id"
                           value="{{ $selectedAccountId }}">


                    {{-- =================================================
                         مبلغ
                         ================================================= --}}
                    <div class="customer-donation-field customer-donation-amount-field">

                        <label for="amount"
                               class="customer-donation-label">

                            مبلغ کمک

                        </label>


                        <div class="customer-donation-amount-wrapper">

                            <input type="text"
                                   id="amount"
                                   name="amount"
                                   class="customer-donation-amount-input js-money-input"
                                   min="50000"
                                   required
                                   inputmode="numeric"
                                   autocomplete="off"
                                   placeholder="مثلاً ۵۰۰٬۰۰۰">

                            <span>
            ریال
        </span>

                        </div>


                        <div class="customer-donation-help-text">

                            <i class="bi bi-info-circle"></i>

                            حداقل مبلغ کمک ۵۰٬۰۰۰ ریال است.

                        </div>

                    </div>


                    {{-- =================================================
                         پرداخت
                         ================================================= --}}
                    <button type="submit"
                            class="customer-donation-submit">

                        <span>
                            <i class="bi bi-credit-card"></i>
                            ادامه پرداخت
                        </span>

                        <i class="bi bi-arrow-left"></i>

                    </button>

                </form>

            </div>

        </section>

    </div>


    {{-- =========================================================
         SCRIPT
         ========================================================= --}}


@endsection

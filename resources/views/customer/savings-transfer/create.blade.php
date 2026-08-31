blade
@extends('customer.layouts.app')

@section('title', 'واریز به حساب پس‌انداز دیگران')

@section('header_title', 'واریز به حساب پس‌انداز دیگران')

@section('header_subtitle', 'واریز وجه به حساب پس‌انداز عضو دیگر صندوق')

@section('content')

    <div class="customer-savings-transfer">

        {{-- =========================================================
             Header
        ========================================================== --}}
        <div class="customer-savings-transfer-header">

            <div class="customer-savings-transfer-header-icon">
                <i class="bi bi-person-plus-fill"></i>
            </div>

            <div class="customer-savings-transfer-header-content">

                <h2>
                    واریز به حساب پس‌انداز دیگران
                </h2>

                <p>
                    شماره حساب پس‌انداز عضو مقصد را وارد کنید.
                </p>

            </div>

        </div>


        {{-- =========================================================
             Main Card
        ========================================================== --}}
        <div class="customer-savings-transfer-card">

            {{-- عنوان --}}
            <div class="customer-savings-transfer-card-title">

                <span class="customer-savings-transfer-card-title-icon">
                    <i class="bi bi-search"></i>
                </span>

                <span>
                    جستجوی حساب مقصد
                </span>

            </div>


            {{-- =====================================================
                 Search
            ====================================================== --}}
            <div class="customer-savings-transfer-search-row">

                <div class="customer-savings-transfer-search-input">

        <span class="customer-savings-transfer-search-icon">
            <i class="bi bi-credit-card-2-front"></i>
        </span>

                    <input
                        id="customer_keyword"
                        type="text"
                        name="keyword"
                        placeholder="مثلاً 3514-6111"
                        inputmode="numeric"
                        autocomplete="off"
                        maxlength="11"
                        required>

                </div>


                <button
                    type="button"
                    id="search_customer"
                    class="customer-savings-transfer-search-button">

                    <i class="bi bi-search"></i>

                    <span>
            جستجو
        </span>

                </button>

            </div>


            <div
                id="customer-result"
                class="customer-savings-transfer-result d-none">
            </div>


            {{-- =====================================================
                 Customer Result
            ====================================================== --}}
            <div
                id="customer-result"
                class="customer-savings-transfer-result d-none">
            </div>


            {{-- =====================================================
                 Payment Form
            ====================================================== --}}
            <form
                method="POST"
                action="{{ route('customer.savings-transfer.store') }}"
                id="savings-transfer-form">

                @csrf


                <input
                    type="hidden"
                    name="receiver_customer_id"
                    id="receiver_customer_id">


                {{-- =================================================
                     Amount
                ================================================== --}}
                <div class="customer-savings-transfer-amount">

                    <label
                        for="amount_display"
                        class="customer-savings-transfer-label">

                        مبلغ واریز

                    </label>

                    <div class="customer-savings-transfer-amount-input">

                        <input
                            type="text"
                            id="amount_display"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="مثلاً ۵۰,۰۰۰"
                            required>

                        <span>
            ریال
        </span>

                    </div>

                    {{-- مقدار واقعی برای ارسال به Laravel --}}
                    <input
                        type="hidden"
                        name="amount"
                        id="amount">

                    @error('amount')

                    <div class="customer-savings-transfer-error">

                        <i class="bi bi-exclamation-circle"></i>

                        {{ $message }}

                    </div>

                    @enderror

                </div>


                {{-- =================================================
                     Payment Button
                ================================================== --}}
                <button
                    type="submit"
                    class="customer-savings-transfer-pay-button"
                    id="payment_button"
                    disabled>

                    <span class="customer-savings-transfer-pay-icon">

                        <i class="bi bi-credit-card-fill"></i>

                    </span>


                    <span class="customer-savings-transfer-pay-content">

                        <strong>
                            پرداخت آنلاین
                        </strong>

                        <small>
                            انتقال وجه به حساب پس‌انداز عضو
                        </small>

                    </span>


                    <i class="bi bi-arrow-left customer-savings-transfer-pay-arrow"></i>

                </button>

            </form>

        </div>

    </div>


    @push('scripts')

        <script>

            const searchUrl =
                "{{ route('customer.savings-transfer.search') }}";

            const csrfToken =
                "{{ csrf_token() }}";

        </script>


        @vite([
        'resources/js/customer/savings-transfer.js'
        ])

    @endpush

@endsection

@extends('customer.layouts.app')

@section('title', 'درخواست وام')

@section('header_title', 'درخواست وام')

@section('header_subtitle', 'ثبت درخواست وام از صندوق')

@section('content')

    @if(session('error'))

        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <span>
            {{ session('error') }}
        </span>

        </div>

    @endif

    @if(session('success'))

        <div class="alert alert-success d-flex align-items-center gap-2 mb-4">

            <i class="bi bi-check-circle-fill"></i>

            <span>
            {{ session('success') }}
        </span>

        </div>

    @endif


    <div class="customer-dashboard customer-loan-request-page">

        {{-- =========================================================
             شرایط وام
        ========================================================== --}}
        <section class="loan-request-info-card loan-request-info-card-blue">

            <div class="loan-request-info-header">

                <div class="loan-request-info-icon">
                    <i class="bi bi-info-circle-fill"></i>
                </div>

                <div>
                    <h2>شرایط وام</h2>
                    <span>قبل از ثبت درخواست، شرایط را مطالعه کنید</span>
                </div>

            </div>

            <ul class="loan-request-info-list">

                <li>
                    وام تا سقف ۴ میلیون تومان با بازپرداخت ۱۰ ماهه.
                </li>

                <li>
                    وام از ۵ تا ۲۰ میلیون تومان با بازپرداخت ۵ ماهه.
                </li>

                <li>
                    وام‌های بیشتر از ۱۰ میلیون تومان پس از بررسی وضعیت مالی،
                    سابقه بازپرداخت و منابع صندوق توسط مدیریت بررسی و تصمیم‌گیری خواهد شد.
                </li>

                <li>
                    وام ازدواج ۱۰ میلیون تومان با بازپرداخت ۲۰ ماهه.
                </li>

            </ul>

        </section>


        {{-- =========================================================
             شرایط ضامن
        ========================================================== --}}
        <section class="loan-request-info-card loan-request-info-card-purple">

            <div class="loan-request-info-header">

                <div class="loan-request-info-icon">
                    <i class="bi bi-shield-check"></i>
                </div>

                <div>
                    <h2>شرایط ضامن</h2>
                    <span>مدارک مورد نیاز برای ضمانت</span>
                </div>

            </div>

            <ul class="loan-request-info-list">

                <li>
                    تمام وام‌ها نیازمند ارائه دو ضامن می‌باشند.
                </li>

                <li>
                    تا سقف ۱۰ میلیون: دو سفته یا دو چک صیادی
                    یا یک سفته و یک چک صیادی.
                </li>

                <li>
                    بالاتر از ۱۰ میلیون: ارائه حداقل یک چک صیادی معتبر الزامی است.
                </li>

            </ul>

        </section>


        {{-- =========================================================
             فرم درخواست وام
        ========================================================== --}}
        <section class="loan-request-form-card">

            <div class="loan-request-form-header">

                <div class="loan-request-form-icon">
                    <i class="bi bi-cash-coin"></i>
                </div>

                <div>
                    <h2>ثبت درخواست وام</h2>
                    <span>
                    مبلغ موردنظر خود را وارد کنید
                </span>
                </div>

            </div>


            <form method="POST"
                  action="{{ route('customer.loan-request.store') }}">

                @csrf


                {{-- مبلغ --}}
                <div class="loan-request-field">

                    <label for="requested_amount">
                        مبلغ درخواستی
                    </label>

                    <div class="loan-request-amount-wrapper">

                        <input
                            type="text"
                            id="requested_amount"
                            name="requested_amount"
                            value="{{ old('requested_amount') }}"
                            inputmode="numeric"
                            autocomplete="off"
                            class="money-input @error('requested_amount') is-invalid @enderror"
                            data-min="10000000"
                            data-max="200000000"
                            placeholder="مثلاً ۵۰,۰۰۰,۰۰۰"
                        >

                        <span>
                        ریال
                    </span>

                    </div>

                    @error('requested_amount')
                    <div class="loan-request-error">
                        {{ $message }}
                    </div>
                    @enderror

                    <div class="loan-request-field-help">
                        مبلغ درخواست باید بین ۱۰,۰۰۰,۰۰۰ تا ۲۰۰,۰۰۰,۰۰۰ ریال باشد.
                    </div>

                </div>


                {{-- توضیحات --}}
                <div class="loan-request-field">

                    <label for="description">
                        توضیحات
                        <span>اختیاری</span>
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        maxlength="1000"
                        class="@error('description') is-invalid @enderror"
                        placeholder="در صورت نیاز توضیحات خود را وارد کنید..."
                    >{{ old('description') }}</textarea>

                    @error('description')
                    <div class="loan-request-error">
                        {{ $message }}
                    </div>
                    @enderror

                </div>


                {{-- دکمه‌ها --}}
                <div class="loan-request-actions">

                    <a href="{{ route('customer.loan-requests.index') }}"
                       class="loan-request-cancel-button">

                        <i class="bi bi-arrow-right"></i>

                        انصراف

                    </a>


                    <button type="submit"
                            class="loan-request-submit-button">

                        <i class="bi bi-send-fill"></i>

                        ثبت درخواست

                    </button>

                </div>

            </form>

        </section>

    </div>


@endsection

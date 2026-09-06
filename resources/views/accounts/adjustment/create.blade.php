@extends('layouts.app')

@section('title', 'اصلاح موجودی حساب')

@section('content')

    <div class="container py-4 adjustment-page">

        {{-- Header --}}
        <div class="adjustment-header">

            <div class="adjustment-title-wrapper">

                <div class="adjustment-title-icon">
                    <i class="bi bi-pencil-square"></i>
                </div>

                <div>
                    <h4 class="adjustment-title">
                        اصلاح موجودی حساب
                    </h4>

                    <div class="adjustment-subtitle">
                        اصلاح موجودی فقط برای اصلاح اشتباهات حسابداری
                    </div>
                </div>

            </div>

            <a href="{{ route('accounts.show', $account) }}"
               class="adjustment-back-btn">

                <i class="bi bi-arrow-right"></i>
                بازگشت

            </a>

        </div>


        {{-- اطلاعات حساب --}}
        <div class="adjustment-card adjustment-account-card">

            <div class="adjustment-card-header">

                <div class="adjustment-section-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <span>
                اطلاعات حساب
            </span>

            </div>


            <div class="adjustment-card-body">

                <div class="adjustment-account-grid">

                    {{-- مالک --}}
                    <div class="adjustment-account-item">

                    <span class="adjustment-label">
                        مالک حساب
                    </span>

                        <strong>
                            @if($account->customer)

                                {{ $account->customer->first_name }}
                                {{ $account->customer->last_name }}

                            @else

                                حساب سیستمی

                            @endif
                        </strong>

                    </div>


                    {{-- شماره حساب --}}
                    <div class="adjustment-account-item">

                    <span class="adjustment-label">
                        شماره حساب
                    </span>

                        <strong dir="ltr">
                            {{ $account->account_number }}
                        </strong>

                    </div>


                    {{-- موجودی --}}
                    <div class="adjustment-account-item adjustment-balance-item">

                    <span class="adjustment-label">
                        موجودی فعلی
                    </span>

                        <strong
                            id="currentBalance"
                            data-value="{{ $account->balance }}"
                        >
                            {{ number_format($account->balance) }}

                            <small>
                                ریال
                            </small>
                        </strong>

                    </div>

                </div>

            </div>

        </div>


        {{-- فرم --}}
        <div class="adjustment-card">

            <div class="adjustment-card-header">

                <div class="adjustment-section-icon">
                    <i class="bi bi-arrow-repeat"></i>
                </div>

                <span>
                ثبت اصلاح موجودی
            </span>

            </div>


            <div class="adjustment-card-body">

                <form
                    method="POST"
                    action="{{ route('accounts.adjustment.store', $account) }}"
                    id="adjustmentForm"
                >

                    @csrf


                    {{-- موجودی جدید --}}
                    <div class="adjustment-field">

                        <label
                            for="new_balance"
                            class="adjustment-form-label"
                        >
                            موجودی صحیح جدید
                        </label>


                        <div class="adjustment-input-wrapper">

                            <input
                                type="text"
                                name="new_balance"
                                id="new_balance"
                                value="{{ old('new_balance') }}"
                                class="form-control money-input adjustment-amount-input @error('new_balance') is-invalid @enderror"
                                inputmode="numeric"
                                autocomplete="off"
                                data-live="true"
                                data-min="0"
                                required
                            >

                            <span class="adjustment-input-unit">
        ریال
    </span>

                        </div>


                        @error('new_balance')

                        <div class="adjustment-error">
                            {{ $message }}
                        </div>

                        @enderror

                    </div>


                    {{-- اختلاف --}}
                    <div
                        id="differenceBox"
                        class="adjustment-difference d-none"
                    >

                        <div class="adjustment-difference-grid">

                            <div>

                            <span>
                                موجودی فعلی
                            </span>

                                <strong id="displayCurrent">
                                    -
                                </strong>

                                <small>
                                    ریال
                                </small>

                            </div>


                            <div>

                            <span>
                                موجودی جدید
                            </span>

                                <strong id="displayNew">
                                    -
                                </strong>

                                <small>
                                    ریال
                                </small>

                            </div>


                            <div>

                            <span>
                                اختلاف
                            </span>

                                <strong id="displayDifference">
                                    -
                                </strong>

                                <small>
                                    ریال
                                </small>

                            </div>

                        </div>


                        <div
                            id="differenceMessage"
                            class="adjustment-difference-message"
                        ></div>

                    </div>





                    {{-- هشدار --}}
                    <div class="adjustment-warning">

                        <i class="bi bi-exclamation-triangle"></i>

                        <span>
                        موجودی جدید جایگزین موجودی فعلی خواهد شد و
                        این عملیات در گردش حساب ثبت می‌شود.
                    </span>

                    </div>


                    {{-- دکمه‌ها --}}
                    <div class="adjustment-actions">

                        <button
                            type="submit"
                            class="adjustment-submit-btn"
                            id="submitButton"
                        >

                            <i class="bi bi-check-circle"></i>

                            <span>
                            ثبت اصلاح موجودی
                        </span>

                        </button>


                        <a
                            href="{{ route('accounts.show', $account) }}"
                            class="adjustment-cancel-btn"
                        >

                            <i class="bi bi-x-circle"></i>

                            <span>
                            انصراف
                        </span>

                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>


    {{-- محاسبه زنده اختلاف --}}
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const input =
                document.getElementById('new_balance');

            const currentBalance =
                Number(
                    document
                        .getElementById('currentBalance')
                        .dataset.value
                );

            const box =
                document.getElementById('differenceBox');

            const displayCurrent =
                document.getElementById('displayCurrent');

            const displayNew =
                document.getElementById('displayNew');

            const displayDifference =
                document.getElementById('displayDifference');

            const message =
                document.getElementById('differenceMessage');


            function clean(value) {

                return Number(
                    String(value)
                        .replace(/,/g, '')
                        .replace(/[۰-۹]/g, function (d) {
                            return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
                        })
                        .replace(/[٠-٩]/g, function (d) {
                            return '٠١٢٣٤٥٦٧٨٩'.indexOf(d);
                        })
                ) || 0;

            }


            function format(value) {

                return Number(value)
                    .toLocaleString('en-US');

            }


            function updateDifference() {

                const newBalance =
                    clean(input.value);


                if (!input.value.trim()) {

                    box.classList.add('d-none');

                    return;

                }


                const difference =
                    newBalance - currentBalance;


                box.classList.remove('d-none');


                displayCurrent.textContent =
                    format(currentBalance);

                displayNew.textContent =
                    format(newBalance);

                displayDifference.textContent =
                    format(Math.abs(difference));


                if (difference > 0) {

                    message.className =
                        'adjustment-difference-message text-success';

                    message.textContent =
                        'موجودی حساب ' +
                        format(difference) +
                        ' ریال افزایش پیدا می‌کند.';

                }

                else if (difference < 0) {

                    message.className =
                        'adjustment-difference-message text-danger';

                    message.textContent =
                        'موجودی حساب ' +
                        format(Math.abs(difference)) +
                        ' ریال کاهش پیدا می‌کند.';

                }

                else {

                    message.className =
                        'adjustment-difference-message text-secondary';

                    message.textContent =
                        'موجودی جدید با موجودی فعلی یکسان است.';

                }

            }


            input.addEventListener(
                'input',
                updateDifference
            );


            updateDifference();

        });

    </script>

@endsection

@extends('layouts.app')

@section('content')

    <div class="container py-4">

        {{-- عنوان --}}
        <div class="mb-4">

            <h4 class="fw-bold mb-1">
                <i class="bi bi-pencil-square text-primary"></i>
                اصلاح موجودی حساب
            </h4>

            <div class="text-muted">
                اصلاح موجودی فقط برای اصلاح اشتباهات حسابداری استفاده می‌شود.
            </div>

        </div>


        {{-- اطلاعات حساب --}}
        <div class="card border border-2 shadow-sm rounded-4 mb-4">

            <div class="card-header fw-bold">

                <i class="bi bi-wallet2 text-primary"></i>

                اطلاعات حساب

            </div>


            <div class="card-body">

                <div class="row text-center">

                    {{-- مالک --}}
                    <div class="col-md-4 mb-3">

                        <small class="text-muted d-block">
                            مالک حساب
                        </small>

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
                    <div class="col-md-4 mb-3">

                        <small class="text-muted d-block">
                            شماره حساب
                        </small>

                        <strong dir="ltr">

                            {{ $account->account_number }}

                        </strong>

                    </div>


                    {{-- موجودی فعلی --}}
                    <div class="col-md-4 mb-3">

                        <small class="text-muted d-block">
                            موجودی فعلی
                        </small>

                        <strong
                            class="text-success fs-5"
                            id="currentBalance"
                            data-value="{{ $account->balance }}"
                        >

                            {{ number_format($account->balance) }}

                            ریال

                        </strong>

                    </div>

                </div>

            </div>

        </div>


        {{-- فرم --}}
        <div class="card border border-2 shadow-sm rounded-4">

            <div class="card-header fw-bold">

                <i class="bi bi-arrow-repeat text-primary"></i>

                ثبت اصلاح موجودی

            </div>


            <div class="card-body p-4">

                <form
                    method="POST"
                    action="{{ route('accounts.adjustment.store', $account) }}"
                    id="adjustmentForm"
                >

                    @csrf


                    {{-- موجودی جدید --}}
                    <div class="mb-4">

                        <label
                            for="new_balance"
                            class="form-label fw-bold"
                        >
                            موجودی صحیح جدید
                        </label>

                        <div class="input-group">

                            <input
                                type="text"
                                name="new_balance"
                                id="new_balance"
                                value="{{ old('new_balance') }}"
                                class="form-control money-input @error('new_balance') is-invalid @enderror"
                                inputmode="numeric"
                                autocomplete="off"
                                data-live="true"
                                data-min="0"
                                required
                            >

                            <span class="input-group-text">
                                ریال
                            </span>

                        </div>


                        @error('new_balance')

                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>

                        @enderror

                    </div>


                    {{-- اختلاف --}}
                    <div
                        id="differenceBox"
                        class="alert alert-secondary d-none"
                    >

                        <div class="row text-center">

                            <div class="col-md-4 mb-2">

                                <small class="text-muted d-block">
                                    موجودی فعلی
                                </small>

                                <strong id="displayCurrent">
                                    -
                                </strong>

                                ریال

                            </div>


                            <div class="col-md-4 mb-2">

                                <small class="text-muted d-block">
                                    موجودی جدید
                                </small>

                                <strong id="displayNew">
                                    -
                                </strong>

                                ریال

                            </div>


                            <div class="col-md-4 mb-2">

                                <small class="text-muted d-block">
                                    اختلاف
                                </small>

                                <strong id="displayDifference">
                                    -
                                </strong>

                                ریال

                            </div>

                        </div>


                        <hr>


                        <div
                            id="differenceMessage"
                            class="text-center fw-bold"
                        ></div>

                    </div>


                    {{-- توضیحات --}}
                    <div class="mb-4">

                        <label
                            for="description"
                            class="form-label fw-bold"
                        >
                            دلیل اصلاح
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            class="form-control @error('description') is-invalid @enderror"
                            maxlength="255"
                            placeholder="دلیل اصلاح موجودی را وارد کنید..."
                        >{{ old('description') }}</textarea>


                        @error('description')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                        @enderror

                    </div>


                    {{-- هشدار --}}
                    <div class="alert alert-warning">

                        <i class="bi bi-exclamation-triangle"></i>

                        توجه: موجودی جدید جایگزین موجودی فعلی خواهد شد و
                        این عملیات در گردش حساب ثبت می‌شود.

                    </div>


                    {{-- دکمه‌ها --}}
                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="submitButton"
                        >

                            <i class="bi bi-check-circle"></i>

                            ثبت اصلاح موجودی

                        </button>


                        <a
                            href="{{ route('accounts.show', $account) }}"
                            class="btn btn-outline-secondary"
                        >

                            انصراف

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
                        'text-center fw-bold text-success';

                    message.innerHTML =
                        'موجودی حساب ' +
                        format(difference) +
                        ' ریال افزایش پیدا می‌کند.';

                } else if (difference < 0) {

                    message.className =
                        'text-center fw-bold text-danger';

                    message.innerHTML =
                        'موجودی حساب ' +
                        format(Math.abs(difference)) +
                        ' ریال کاهش پیدا می‌کند.';

                } else {

                    message.className =
                        'text-center fw-bold text-secondary';

                    message.innerHTML =
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

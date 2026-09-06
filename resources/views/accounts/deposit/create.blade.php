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
                    <h4 class="adjustment-title">اصلاح موجودی حساب</h4>
                    <div class="adjustment-subtitle">
                        فقط برای اصلاح اشتباهات حسابداری
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

                <span>اطلاعات حساب</span>
            </div>

            <div class="adjustment-card-body">

                <div class="adjustment-account-grid">

                    <div class="adjustment-account-item">
                        <span class="adjustment-label">مالک حساب</span>

                        <strong>
                            @if($account->customer)
                                {{ $account->customer->first_name }}
                                {{ $account->customer->last_name }}
                            @else
                                حساب سیستمی
                            @endif
                        </strong>
                    </div>

                    <div class="adjustment-account-item">
                        <span class="adjustment-label">شماره حساب</span>

                        <strong dir="ltr">
                            {{ $account->account_number }}
                        </strong>
                    </div>

                    <div class="adjustment-account-item adjustment-balance-item">
                        <span class="adjustment-label">موجودی فعلی</span>

                        <strong id="currentBalance"
                                data-value="{{ $account->balance }}">
                            {{ number_format($account->balance) }}
                            <small>ریال</small>
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

                <span>ثبت اصلاح موجودی</span>
            </div>

            <div class="adjustment-card-body">

                <form method="POST"
                      action="{{ route('accounts.adjustment.store', $account) }}"
                      id="adjustmentForm">

                    @csrf

                    {{-- موجودی جدید --}}
                    <div class="adjustment-field">

                        <label for="new_balance"
                               class="adjustment-form-label">
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
                    <div id="differenceBox"
                         class="adjustment-difference d-none">

                        <div class="adjustment-difference-grid">

                            <div>
                                <span>موجودی فعلی</span>
                                <strong id="displayCurrent">-</strong>
                                <small>ریال</small>
                            </div>

                            <div>
                                <span>موجودی جدید</span>
                                <strong id="displayNew">-</strong>
                                <small>ریال</small>
                            </div>

                            <div>
                                <span>اختلاف</span>
                                <strong id="displayDifference">-</strong>
                                <small>ریال</small>
                            </div>

                        </div>

                        <div id="differenceMessage"
                             class="adjustment-difference-message">
                        </div>

                    </div>


                    {{-- توضیحات --}}
                    <div class="adjustment-field">

                        <label for="description"
                               class="adjustment-form-label">
                            دلیل اصلاح
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            maxlength="255"
                            class="form-control adjustment-description @error('description') is-invalid @enderror"
                            placeholder="دلیل اصلاح موجودی را وارد کنید..."
                        >{{ old('description') }}</textarea>

                        @error('description')
                        <div class="adjustment-error">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>


                    {{-- هشدار --}}
                    <div class="adjustment-warning">
                        <i class="bi bi-exclamation-triangle"></i>

                        <span>
                        موجودی جدید جایگزین موجودی فعلی می‌شود و
                        عملیات در گردش حساب ثبت خواهد شد.
                    </span>
                    </div>


                    {{-- دکمه‌ها --}}
                    <div class="adjustment-actions">

                        <button type="submit"
                                class="adjustment-submit-btn"
                                id="submitButton">
                            <i class="bi bi-check-circle"></i>
                            ثبت اصلاح
                        </button>

                        <a href="{{ route('accounts.show', $account) }}"
                           class="adjustment-cancel-btn">
                            انصراف
                        </a>

                    </div>

                </form>

            </div>
        </div>

    </div>

@endsection

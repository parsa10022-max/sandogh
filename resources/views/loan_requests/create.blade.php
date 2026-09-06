@extends('layouts.app')

@section('title', 'ثبت درخواست وام')

@section('content')

    <div class="container-fluid loan-request-create-page">

        {{-- Header --}}
        <x-page-header title="ثبت درخواست وام">
            <a href="{{ route('loan-requests.index') }}"
               class="btn loan-request-back-btn">
                <i class="bi bi-arrow-right"></i>
                <span>بازگشت به درخواست‌ها</span>
            </a>
        </x-page-header>


        {{-- شرایط وام --}}
        <div class="loan-request-conditions-card">

            <div class="loan-request-conditions-header">
                <div class="loan-request-conditions-icon loan-icon-purple">
                    <i class="bi bi-info-circle"></i>
                </div>

                <div>
                    <h5>شرایط وام</h5>
                    <p>لطفاً قبل از ثبت درخواست، شرایط دریافت وام را مطالعه کنید.</p>
                </div>
            </div>

            <div class="loan-request-conditions-body">

                <div class="loan-condition-item">
                    <span class="loan-condition-icon">
                        <i class="bi bi-check2"></i>
                    </span>
                    <span>وام تا سقف <strong>۴ میلیون تومان</strong> با بازپرداخت ۱۰ ماهه.</span>
                </div>

                <div class="loan-condition-item">
                    <span class="loan-condition-icon">
                        <i class="bi bi-check2"></i>
                    </span>
                    <span>وام از <strong>۵ تا ۲۰ میلیون تومان</strong> با بازپرداخت ۵ ماهه.</span>
                </div>

                <div class="loan-condition-item">
                    <span class="loan-condition-icon">
                        <i class="bi bi-check2"></i>
                    </span>
                    <span>
                        وام‌های بیشتر از ۱۰ میلیون تومان پس از بررسی وضعیت مالی،
                        سابقه بازپرداخت و منابع صندوق، توسط مدیریت بررسی و تصمیم‌گیری خواهد شد.
                    </span>
                </div>

                <div class="loan-condition-item">
                    <span class="loan-condition-icon">
                        <i class="bi bi-check2"></i>
                    </span>
                    <span>
                        وام ازدواج <strong>۱۰ میلیون تومان</strong> با بازپرداخت ۲۰ ماهه.
                    </span>
                </div>

            </div>

        </div>


        {{-- شرایط ضامن --}}
        <div class="loan-request-conditions-card guarantor-card">

            <div class="loan-request-conditions-header">
                <div class="loan-request-conditions-icon loan-icon-orange">
                    <i class="bi bi-shield-check"></i>
                </div>

                <div>
                    <h5>شرایط ضامن</h5>
                    <p>مدارک و شرایط مورد نیاز برای ضمانت وام</p>
                </div>
            </div>

            <div class="loan-request-conditions-body">

                <div class="loan-condition-item">
                    <span class="loan-condition-icon">
                        <i class="bi bi-check2"></i>
                    </span>
                    <span>تمام وام‌ها نیازمند ارائه <strong>دو ضامن</strong> می‌باشند.</span>
                </div>

                <div class="loan-condition-item">
                    <span class="loan-condition-icon">
                        <i class="bi bi-check2"></i>
                    </span>
                    <span>
                        تا سقف <strong>۱۰ میلیون تومان</strong>:
                        دو سفته، دو چک صیادی یا یک سفته و یک چک صیادی.
                    </span>
                </div>

                <div class="loan-condition-item">
                    <span class="loan-condition-icon">
                        <i class="bi bi-check2"></i>
                    </span>
                    <span>
                        بالاتر از <strong>۱۰ میلیون تومان</strong>،
                        ارائه حداقل یک چک صیادی معتبر الزامی است.
                    </span>
                </div>

            </div>

        </div>


        {{-- فرم --}}
        <div class="loan-request-form-card">

            <div class="loan-request-form-header">

                <div class="loan-request-form-header-icon">
                    <i class="bi bi-file-earmark-plus"></i>
                </div>

                <div>
                    <h5>اطلاعات درخواست</h5>
                    <p>اطلاعات درخواست وام را وارد کنید.</p>
                </div>

            </div>


            <div class="loan-request-form-body">

                <form method="POST"
                      action="{{ route('loan-requests.store') }}">

                    @csrf

                    <div class="row g-3">

                        {{-- مشتری --}}
                        <div class="col-12">

                            <x-inputs.select-input
                                name="customer_id"
                                label="مشتری"
                                :options="$customers->pluck('full_name', 'id')->toArray()"
                                :value="old('customer_id')"
                                required
                            />

                        </div>


                        {{-- مبلغ --}}
                        <div class="col-12 col-md-6">

                            <div class="loan-request-money-field">

                                <label for="requested_amount"
                                       class="form-label">
                                    مبلغ درخواستی
                                    <span class="text-danger">*</span>
                                </label>

                                <div class="loan-request-money-input">

                                    <input type="number"
                                           id="requested_amount"
                                           name="requested_amount"
                                           min="0"
                                           inputmode="numeric"
                                           class="form-control @error('requested_amount') is-invalid @enderror"
                                           value="{{ old('requested_amount') }}"
                                           placeholder="مثلاً 10000000">

                                    <span>تومان</span>

                                </div>

                                @error('requested_amount')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>

                        </div>


                        {{-- توضیحات --}}
                        <div class="col-12">

                            <div class="loan-request-description-field">

                                <label for="description"
                                       class="form-label">
                                    توضیحات
                                </label>

                                <textarea id="description"
                                          name="description"
                                          rows="5"
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="در صورت نیاز توضیحات مربوط به درخواست وام را وارد کنید...">{{ old('description') }}</textarea>

                                @error('description')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>

                        </div>

                    </div>


                    {{-- Actions --}}
                    <div class="loan-request-form-actions">

                        <a href="{{ route('loan-requests.index') }}"
                           class="btn loan-request-cancel-btn">

                            <i class="bi bi-x-lg"></i>
                            <span>انصراف</span>

                        </a>

                        <button type="submit"
                                class="btn loan-request-submit-btn">

                            <i class="bi bi-check-lg"></i>
                            <span>ثبت درخواست</span>

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection

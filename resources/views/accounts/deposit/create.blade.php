@extends('layouts.app')

@section('title', 'واریز به حساب')

@section('content')

    <div class="container py-4">

        <div class="card shadow-sm border-0">

            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-arrow-up-circle text-success"></i>
                    واریز به حساب
                </h5>
            </div>

            <div class="card-body">

                {{-- اطلاعات حساب --}}
                <div class="mb-4">

                    <div class="mb-2">
                        <strong>عضو:</strong>

                        {{ $account->customer->first_name }}
                        {{ $account->customer->last_name }}
                    </div>

                    <div class="mb-2">
                        <strong>شماره حساب:</strong>

                        <span dir="ltr">
                            {{ $account->account_number }}
                        </span>
                    </div>

                    <div>
                        <strong>موجودی فعلی:</strong>

                        <span class="fw-bold">
                            {{ number_format($account->balance) }}
                            ریال
                        </span>
                    </div>

                </div>

                <hr>

                <form method="POST"
                      action="{{ route('accounts.deposit') }}">

                    @csrf

                    <input type="hidden"
                           name="account_id"
                           value="{{ $account->id }}">

                    {{-- مبلغ --}}
                    <div class="mb-3">

                        <label for="amount" class="form-label">
                            مبلغ واریز
                        </label>

                        <div class="input-group">

                            <input
                                type="text"
                                name="amount"
                                id="amount"
                                value="{{ old('amount') }}"
                                class="form-control money-input @error('amount') is-invalid @enderror"
                                inputmode="numeric"
                                autocomplete="off"
                                data-min="50000"
                                required
                            >

                            <span class="input-group-text">
                                ریال
                            </span>

                        </div>

                        @error('amount')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                        @enderror

                        <div class="form-text">
                            حداقل مبلغ واریز ۵۰,۰۰۰ ریال است.
                        </div>

                    </div>

                    {{-- روش پرداخت --}}
                    <div class="mb-3">

                        <label for="payment_method" class="form-label">
                            نوع واریز
                        </label>

                        <select
                            name="payment_method"
                            id="payment_method"
                            class="form-select @error('payment_method') is-invalid @enderror"
                            required
                        >

                            <option value="">
                                انتخاب کنید
                            </option>

                            <option value="1"
                                    @selected(old('payment_method') == 1)>
                            نقدی
                            </option>

                            <option value="2"
                                    @selected(old('payment_method') == 2)>
                            دستگاه پوز
                            </option>

                            <option value="3"
                                    @selected(old('payment_method') == 3)>
                            درگاه آنلاین
                            </option>

                            <option value="4"
                                    @selected(old('payment_method') == 4)>
                            وام
                            </option>

                        </select>

                        @error('payment_method')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                    {{-- توضیحات --}}
                    <div class="mb-4">

                        <label for="description" class="form-label">
                            توضیحات
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            class="form-control @error('description') is-invalid @enderror"
                            rows="3"
                        >{{ old('description') }}</textarea>

                        @error('description')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

                    {{-- دکمه‌ها --}}
                    <div class="d-flex gap-2">

                        <button type="submit"
                                class="btn btn-success">

                            <i class="bi bi-check-circle"></i>
                            ثبت واریز

                        </button>

                        <a href="{{ route('accounts.show', $account) }}"
                           class="btn btn-outline-secondary">

                            انصراف

                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection

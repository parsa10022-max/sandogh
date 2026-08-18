@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <div class="mb-4">
            <h4 class="fw-bold mb-1">تعریف حساب مشتری</h4>
            <div class="text-muted">
                {{ $customer->full_name }}
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <form method="POST"
                      action="{{ route('customers.accounts.store', $customer) }}">

                    @csrf

                    {{-- نوع حساب --}}
                    <div class="mb-3">
                        <label for="account_type" class="form-label">
                            نوع حساب
                        </label>

                        <select
                            name="account_type"
                            id="account_type"
                            class="form-select @error('account_type') is-invalid @enderror"
                            required
                        >
                            <option value="">انتخاب کنید</option>

                            <option value="1"
                                    data-prefix="6111"
                                    @selected(old('account_type') == 1)>
                            پس‌انداز
                            </option>

                            <option value="2"
                                    data-prefix="6112"
                                    @selected(old('account_type') == 2)>
                            جاری
                            </option>
                        </select>

                        @error('account_type')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    {{-- شماره حساب --}}
                    <div class="mb-3">
                        <label for="account_number_suffix" class="form-label">
                            شماره حساب
                        </label>

                        <div class="loan-number-box">
                            <span
                                id="account-prefix"
                                class="loan-prefix"
                            >----</span>

                            <input
                                type="text"
                                id="account_number_suffix"
                                class="form-control"
                                inputmode="numeric"
                                maxlength="16"
                                placeholder="شماره حساب"
                            >
                        </div>

                        <input
                            type="hidden"
                            name="account_number"
                            id="account_number"
                            value="{{ old('account_number') }}"
                        >

                        @error('account_number')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    {{-- موجودی اولیه --}}
                    <div class="mb-3">
                        <label for="balance" class="form-label">
                            موجودی اولیه
                        </label>

                        <div class="input-group">
                            <input
                                type="text"
                                name="balance"
                                id="balance"
                                value="{{ old('balance', 0) }}"
                                class="form-control money-input @error('balance') is-invalid @enderror"
                                inputmode="numeric"
                                autocomplete="off"
                                required
                            >

                            <span class="input-group-text">
                                ریال
                            </span>
                        </div>

                        @error('balance')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">

                        <button type="submit" class="btn btn-primary">
                            ثبت حساب
                        </button>

                        <a href="{{ route('customers.show', $customer) }}"
                           class="btn btn-outline-secondary">
                            انصراف
                        </a>

                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection

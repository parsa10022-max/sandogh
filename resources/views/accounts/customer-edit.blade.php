@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <div class="mb-4">
            <h4 class="fw-bold mb-1">ویرایش حساب مشتری</h4>

            <div class="text-muted">
                {{ $customer->full_name }}
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <form method="POST"
                      action="{{ route('customers.accounts.update', [$customer, $account]) }}">

                    @csrf
                    @method('PUT')

                    {{-- شماره حساب --}}
                    <div class="mb-3">

                        <label class="form-label">
                            شماره حساب
                        </label>

                        @php
                            $accountParts = explode('-', $account->account_number);
                            $accountSuffix = $accountParts[1] ?? '';
                        @endphp

                        <div class="account-number-box">

                            <span id="account-prefix" class="account-prefix">
                                {{ $account->account_type->prefix() }}-
                            </span>

                            <input
                                type="text"
                                id="account_number_suffix"
                                name="account_number_suffix"
                                value="{{ old('account_number_suffix', $accountSuffix) }}"
                                class="account-suffix"
                                maxlength="16"
                                inputmode="numeric"
                                autocomplete="off"
                                required
                            >

                        </div>

                        <input
                            type="hidden"
                            name="account_number"
                            id="account_number"
                            value="{{ old('account_number', $account->account_number) }}"
                        >

                        @error('account_number')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                        @enderror

                        @error('account_number_suffix')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>

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

                            <option value="1"
                                    data-prefix="6111"
                                    @selected(old('account_type', $account->account_type->value) == 1)>
                            پس‌انداز
                            </option>

                            <option value="2"
                                    data-prefix="6112"
                                    @selected(old('account_type', $account->account_type->value) == 2)>
                            جاری
                            </option>

                        </select>

                        @error('account_type')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror

                    </div>



                    {{-- دکمه‌ها --}}
                    <div class="d-flex gap-2">

                        <button type="submit" class="btn btn-primary">
                            ذخیره تغییرات
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

@extends('layouts.app')

@section('title', 'پروفایل مشتری')

@section('content')

    <div class="container-fluid customer-show-page">

        {{-- =====================================================
             PAGE HEADER
        ====================================================== --}}

        <div class="customer-show-card customer-show-card--header">

            <div class="customer-show-card__header">

                <div class="customer-show-card__title">

                    <div class="customer-show-card__icon">
                        <i class="bi bi-person-vcard"></i>
                    </div>

                    <div>

                        <h1>
                            پروفایل مشتری
                        </h1>

                        <p>
                            اطلاعات مشتری و حساب‌های مالی
                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             TWO COLUMN CONTENT
        ====================================================== --}}

        <div class="customer-show-grid">


            {{-- =================================================
                 CUSTOMER INFORMATION
            ================================================== --}}

            <div class="customer-info-box">

                <div class="customer-info-box__header">

                    <div class="customer-info-box__icon">
                        <i class="bi bi-person-vcard"></i>
                    </div>

                    <div>
                        <h2 class="customer-info-box__title">
                            اطلاعات مشتری
                        </h2>

                        <p class="customer-info-box__description">
                            اطلاعات هویتی و تماس مشتری
                        </p>
                    </div>

                </div>


                <div class="table-responsive">

                    <table class="table customer-info-table align-middle">

                        <tbody>

                        {{-- کد مشتری --}}
                        <tr>
                            <th>
                                کد مشتری
                            </th>

                            <td>
                                <span class="customer-value customer-value--primary">
                                    {{ $customer->customer_code }}
                                </span>
                            </td>
                        </tr>


                        {{-- نام --}}
                        <tr>
                            <th>
                                نام
                            </th>

                            <td>
                                {{ $customer->full_name }}
                            </td>
                        </tr>


                        {{-- نام پدر --}}
                        <tr>
                            <th>
                                نام پدر
                            </th>

                            <td>
                                {{ $customer->father_name ?: '—' }}
                            </td>
                        </tr>


                        {{-- کد ملی --}}
                        <tr>
                            <th>
                                کد ملی
                            </th>

                            <td dir="ltr">
                                {{ $customer->national_code }}
                            </td>
                        </tr>


                        {{-- شماره شبا --}}
                        <tr>
                            <th>
                                شماره شبا
                            </th>

                            <td dir="ltr">
                                {{ \App\Support\Iban::format($customer->iban) }}
                            </td>
                        </tr>


                        {{-- بانک --}}
                        <tr>
                            <th>
                                بانک
                            </th>

                            <td>
                                {{ \App\Support\Iban::bankName($customer->iban) ?: '—' }}
                            </td>
                        </tr>


                        {{-- موبایل --}}
                        <tr>
                            <th>
                                موبایل
                            </th>

                            <td dir="ltr">
                                {{ $customer->mobile }}
                            </td>
                        </tr>


                        {{-- موبایل دوم --}}
                        <tr>
                            <th>
                                موبایل دوم
                            </th>

                            <td dir="ltr">
                                {{ $customer->mobile_second ?: '—' }}
                            </td>
                        </tr>


                        {{-- وضعیت --}}
                        <tr>
                            <th>
                                وضعیت
                            </th>

                            <td>
                                <span class="customer-status">
                                    {{ $customer->status->label() ?? $customer->status->value }}
                                </span>
                            </td>
                        </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- =================================================
                 CUSTOMER ACCOUNTS
            ================================================== --}}

            <div class="customer-accounts-box">

                <div class="customer-accounts-card">

                    <div class="customer-accounts-header">

                        <div class="customer-accounts-title">

                            <div class="customer-accounts-title__icon">
                                <i class="bi bi-bank"></i>
                            </div>

                            <div>

                                <h2>
                                    حساب‌های مشتری
                                </h2>

                                <p>
                                    حساب‌های مالی ثبت‌شده برای این مشتری
                                </p>

                            </div>

                        </div>


                        <a
                            href="{{ route('customers.accounts.create', $customer) }}"
                            class="btn customer-add-account-btn"
                        >

                            <i class="bi bi-plus-circle"></i>

                            <span>
                                تعریف حساب
                            </span>

                        </a>

                    </div>


                    {{-- =================================================
                         ACCOUNTS TABLE
                    ================================================== --}}

                    @if($customer->accounts->count())

                        <div class="customer-accounts-table-wrapper">

                            <table class="table customer-accounts-table align-middle">

                                <thead>

                                <tr>

                                    <th>
                                        شماره حساب
                                    </th>

                                    <th>
                                        نوع حساب
                                    </th>

                                    <th>
                                        موجودی
                                    </th>

                                    <th>
                                        وضعیت
                                    </th>

                                    <th>
                                        عملیات
                                    </th>

                                </tr>

                                </thead>


                                <tbody>

                                @foreach($customer->accounts as $account)

                                    <tr>

                                        {{-- شماره حساب --}}
                                        <td>

                                            <a
                                                href="{{ route('accounts.show', $account) }}"
                                                class="customer-account-number"
                                            >
                                                {{ $account->account_number }}
                                            </a>

                                        </td>


                                        {{-- نوع حساب --}}
                                        <td>

                                            <span class="customer-account-type">
                                                {{ $account->account_type->label() }}
                                            </span>

                                        </td>


                                        {{-- موجودی --}}
                                        <td>

                                            <span class="customer-account-balance">

                                                {{ number_format($account->balance) }}

                                                <span class="customer-account-balance__unit">
                                                    ریال
                                                </span>

                                            </span>

                                        </td>


                                        {{-- وضعیت --}}
                                        <td>

                                            <span class="customer-account-status">
                                                {{ $account->status->label() }}
                                            </span>

                                        </td>


                                        {{-- عملیات --}}
                                        <td>

                                            <a
                                                href="{{ route('customers.accounts.edit', [$customer, $account]) }}"
                                                class="btn customer-account-edit-btn"
                                                title="ویرایش حساب"
                                            >

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                        </td>

                                    </tr>

                                @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        {{-- =================================================
                             EMPTY STATE
                        ================================================== --}}

                        <div class="customer-empty-accounts">

                            <div class="customer-empty-accounts__icon">
                                <i class="bi bi-bank"></i>
                            </div>

                            <span>
                                هنوز حسابی برای این مشتری تعریف نشده است.
                            </span>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

@endsection

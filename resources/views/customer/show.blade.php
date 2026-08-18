@extends('layouts.app')

@section('title', 'پروفایل مشتری')

@section('content')

    <div class="container-fluid">

        <div class="card shadow-sm">

            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-vcard"></i>
                    پروفایل مشتری
                </h5>
            </div>

            <div class="card-body">

                {{-- اطلاعات مشتری --}}
                <table class="table table-bordered align-middle">

                    <tr>
                        <th width="200">کد مشتری</th>
                        <td>{{ $customer->customer_code }}</td>
                    </tr>

                    <tr>
                        <th>نام</th>
                        <td>{{ $customer->full_name }}</td>
                    </tr>

                    <tr>
                        <th>نام پدر</th>
                        <td>{{ $customer->father_name }}</td>
                    </tr>

                    <tr>
                        <th>کد ملی</th>
                        <td>{{ $customer->national_code }}</td>
                    </tr>

                    <tr>
                        <th>شماره شبا</th>
                        <td>
                            {{ \App\Support\Iban::format($customer->iban) }}
                        </td>
                    </tr>

                    <tr>
                        <th>بانک</th>
                        <td>
                            {{ \App\Support\Iban::bankName($customer->iban) }}
                        </td>
                    </tr>

                    <tr>
                        <th>موبایل</th>
                        <td>{{ $customer->mobile }}</td>
                    </tr>

                    <tr>
                        <th>موبایل دوم</th>
                        <td>{{ $customer->mobile_second }}</td>
                    </tr>

                    <tr>
                        <th>وضعیت</th>
                        <td>
                            {{ $customer->status->label() ?? $customer->status->value }}
                        </td>
                    </tr>

                </table>

                <hr class="my-4">

                {{-- حساب‌های مشتری --}}
                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h5 class="mb-0">
                        <i class="bi bi-bank"></i>
                        حساب‌های مشتری
                    </h5>

                    <a href="{{ route('customers.accounts.create', $customer) }}"
                       class="btn btn-primary btn-sm">

                        <i class="bi bi-plus-circle"></i>
                        تعریف حساب

                    </a>

                </div>

                @if($customer->accounts->count())

                    <div class="table-responsive">

                        <table class="table table-bordered align-middle">

                            <thead>
                            <tr>
                                <th>شماره حساب</th>
                                <th>نوع حساب</th>
                                <th>موجودی اولیه</th>
                                <th>وضعیت</th>
                                <th width="100">عملیات</th>
                            </tr>
                            </thead>

                            <tbody>

                            @foreach($customer->accounts as $account)

                                <tr>

                                    <td>
                                        <a href="{{ route('accounts.show', $account) }}"
                                           class="text-decoration-none fw-semibold">
                                            {{ $account->account_number }}
                                        </a>
                                    </td>

                                    <td>
                                        {{ $account->account_type->label() }}
                                    </td>

                                    <td>
                                        {{ number_format($account->balance) }}
                                        ریال
                                    </td>

                                    <td>
                                        {{ $account->status->label() }}
                                    </td>

                                    <td>

                                        <a href="{{ route('customers.accounts.edit', [$customer, $account]) }}"
                                           class="btn btn-outline-primary btn-sm"
                                           title="ویرایش حساب">

                                            <i class="bi bi-pencil"></i>

                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    <div class="alert alert-light border text-center mb-0">
                        هنوز حسابی برای این مشتری تعریف نشده است.
                    </div>

                @endif

            </div>

        </div>

    </div>

@endsection

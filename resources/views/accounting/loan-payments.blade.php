@extends('layouts.app')

@section('title', 'پرداخت اقساط')

@section('content')

    @push('styles')
        @vite('resources/css/admin/accounting/loan-payments.css')
    @endpush

    <div class="container-fluid loan-payments-page">

        {{-- Header --}}
        <div class="loan-payments-header">

            <div class="loan-payments-title-wrapper">

                <div class="loan-payments-title-icon">
                    <i class="bi bi-credit-card"></i>
                </div>

                <div>
                    <h4 class="loan-payments-title">
                        پرداخت اقساط
                    </h4>

                    <div class="loan-payments-subtitle">
                        پرداخت‌های انجام‌شده در انتظار ثبت حسابداری
                    </div>
                </div>

            </div>

            <a href="{{ route('admin.accounting.index') }}"
               class="loan-payments-back-btn">

                <i class="bi bi-arrow-right"></i>

                <span>حسابداری</span>

            </a>

        </div>


        {{-- Table Card --}}
        <div class="loan-payments-card">

            <div class="loan-payments-card-header">

                <div class="loan-payments-card-title">

                    <div class="loan-payments-card-icon">
                        <i class="bi bi-list-check"></i>
                    </div>

                    <div>
                        <div>
                            پرداخت‌های در انتظار ثبت
                        </div>

                        <small>
                            بررسی و تأیید پرداخت‌های اقساط
                        </small>
                    </div>

                </div>

                <div class="loan-payments-count">
                    {{ $payments->total() }} مورد
                </div>

            </div>


            <div class="loan-payments-card-body">

                <div class="table-responsive loan-payments-table-wrapper">

                    <table class="table align-middle loan-payments-table">

                        <thead>

                        <tr>

                            <th>#</th>

                            <th>شماره وام</th>

                            <th>عضو</th>

                            <th>قسط</th>

                            <th>مبلغ</th>

                            <th>پرداخت‌کننده</th>

                            <th>تاریخ پرداخت</th>

                            <th class="text-center">عملیات</th>

                        </tr>

                        </thead>


                        <tbody>

                        @forelse($payments as $payment)

                            <tr>

                                {{-- شماره --}}
                                <td>

                                    <span class="loan-payment-row-number">
                                        {{ $payments->firstItem() + $loop->index }}
                                    </span>

                                </td>


                                {{-- شماره وام --}}
                                <td>

                                    <span class="loan-payment-loan-number" dir="ltr">
                                        {{ $payment->loan?->loan_number ?? '---' }}
                                    </span>

                                </td>


                                {{-- عضو --}}
                                <td>

                                    @if($payment->loan?->customer)

                                        <div class="loan-payment-customer">

                                            <div class="loan-payment-customer-icon">
                                                <i class="bi bi-person"></i>
                                            </div>

                                            <div class="loan-payment-customer-name">

                                                {{ $payment->loan->customer->first_name }}
                                                {{ $payment->loan->customer->last_name }}

                                            </div>

                                        </div>

                                    @else

                                        <span class="loan-payment-muted">
                                            ---
                                        </span>

                                    @endif

                                </td>


                                {{-- شماره قسط --}}
                                <td>

                                    @if($payment->installment)

                                        <span class="loan-payment-installment">

                                            {{ $payment->installment->installment_number
                                                ?? $payment->installment->number
                                                ?? 0 }}

                                        </span>

                                    @else

                                        <span class="loan-payment-muted">
                                            ---
                                        </span>

                                    @endif

                                </td>


                                {{-- مبلغ --}}
                                <td>

                                    <div class="loan-payment-amount">

                                        <strong>
                                            {{ number_format($payment->amount ?? 0) }}
                                        </strong>

                                        <small>
                                            تومان
                                        </small>

                                    </div>

                                </td>


                                {{-- پرداخت‌کننده --}}
                                <td>

                                    @if($payment->user)

                                        <span class="loan-payment-user">
                                            <i class="bi bi-person-check"></i>
                                            {{ $payment->user->name }}
                                        </span>

                                    @else

                                        <span class="loan-payment-muted">
                                            ---
                                        </span>

                                    @endif

                                </td>


                                {{-- تاریخ --}}
                                <td>

                                    @if($payment->paid_at)

                                        <div class="loan-payment-date">

                                            <span>
                                                {{ $payment->paid_at->format('Y/m/d') }}
                                            </span>

                                            <small>
                                                {{ $payment->paid_at->format('H:i') }}
                                            </small>

                                        </div>

                                    @else

                                        <span class="loan-payment-muted">
                                            ---
                                        </span>

                                    @endif

                                </td>


                                {{-- عملیات --}}
                                <td class="text-center">

                                    <form method="POST"
                                          action="{{ route(
                                              'admin.accounting.confirm',
                                              [
                                                  'type' => 'loan-payment',
                                                  'id' => $payment->id
                                              ]
                                          ) }}"
                                          class="loan-payment-confirm-form"
                                          onsubmit="return confirm('آیا این پرداخت قسط به عنوان ثبت‌شده در حسابداری تأیید شود؟')">

                                        @csrf

                                        <button type="submit"
                                                class="loan-payment-confirm-btn">

                                            <i class="bi bi-check2-circle"></i>

                                            <span>
                                                تأیید ثبت
                                            </span>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8">

                                    <div class="loan-payments-empty">

                                        <div class="loan-payments-empty-icon">
                                            <i class="bi bi-check-circle"></i>
                                        </div>

                                        <h6>
                                            پرداخت قسطی در انتظار ثبت وجود ندارد
                                        </h6>

                                        <p>
                                            تمام پرداخت‌های اقساط در حسابداری ثبت شده‌اند.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- Pagination --}}
        @if($payments->hasPages())

            <div class="loan-payments-pagination">

                {{ $payments->links() }}

            </div>

        @endif

    </div>

@endsection

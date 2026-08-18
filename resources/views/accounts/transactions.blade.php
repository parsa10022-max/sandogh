@extends('layouts.app')

@section('content')

    <div class="container py-4">

        {{-- خلاصه حساب --}}
        <div class="card border border-2 shadow-sm mb-4">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div class="fw-bold">

                    <i class="bi bi-wallet2 text-primary"></i>

                    گردش حساب

                </div>

                <span class="badge bg-primary fs-6" dir="ltr">
                    {{ $account->account_number }}
                </span>

            </div>


            <div class="card-body">

                <div class="row text-center">

                    {{-- عنوان حساب --}}
                    <div class="col-md-4 mb-3">

                        <small class="text-muted">
                            عنوان حساب
                        </small>

                        <div class="fw-bold">

                            {{ $account->name ?? 'حساب مشتری' }}

                        </div>

                    </div>


                    {{-- مالک حساب --}}
                    <div class="col-md-4 mb-3">

                        <small class="text-muted">
                            مالک حساب
                        </small>

                        <div class="fw-bold">

                            @if($account->customer)

                                {{ $account->customer->first_name }}
                                {{ $account->customer->last_name }}

                            @else

                                حساب سیستمی

                            @endif

                        </div>

                    </div>


                    {{-- موجودی فعلی --}}
                    <div class="col-md-4 mb-3">

                        <small class="text-muted">
                            موجودی فعلی
                        </small>

                        <div class="fw-bold text-success fs-5">

                            {{ number_format($account->balance) }}

                            ریال

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- جدول تراکنش‌ها --}}
        <div class="card border border-2 shadow-sm">

            <div class="card-header fw-bold">

                تراکنش‌ها

            </div>


            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover text-center align-middle">

                        <thead class="table-light">

                        <tr>

                            <th>
                                تاریخ
                            </th>

                            <th>
                                شماره تراکنش
                            </th>

                            <th>
                                روش پرداخت
                            </th>

                            <th>
                                نوع تراکنش
                            </th>

                            <th>
                                مبلغ
                            </th>

                            <th>
                                مانده
                            </th>

                            <th>
                                توضیح
                            </th>

                        </tr>

                        </thead>


                        <tbody>

                        @forelse($transactions as $transaction)

                            <tr>

                                {{-- تاریخ --}}
                                <td>

                                    {{ app(\App\Services\Date\JalaliDateService::class)
                                        ->toJalali($transaction->transaction_date) }}

                                </td>


                                {{-- شماره تراکنش --}}
                                <td dir="ltr">

                                    {{ $transaction->transaction_no }}

                                </td>


                                {{-- روش پرداخت --}}
                                <td>

                                    {{ $transaction->payment_method?->label() ?? '-' }}

                                </td>


                                {{-- نوع تراکنش --}}
                                <td>

                                    @if($transaction->transaction_type === \App\Enums\TransactionType::DEPOSIT)

                                        <span class="badge bg-success">

                                            {{ $transaction->transaction_type->label() }}

                                        </span>

                                    @elseif($transaction->transaction_type === \App\Enums\TransactionType::WITHDRAWAL)

                                        <span class="badge bg-danger">

                                            {{ $transaction->transaction_type->label() }}

                                        </span>

                                    @elseif($transaction->transaction_type === \App\Enums\TransactionType::ADJUSTMENT)

                                        <span class="badge bg-warning text-dark">

                                            {{ $transaction->transaction_type->label() }}

                                        </span>

                                    @else

                                        <span class="badge bg-secondary">

                                            {{ $transaction->transaction_type->label() }}

                                        </span>

                                    @endif

                                </td>


                                {{-- مبلغ --}}
                                <td class="fw-bold">

                                    {{ number_format($transaction->amount) }}

                                    ریال

                                </td>


                                {{-- مانده بعد از تراکنش --}}
                                <td class="fw-bold">

                                    {{ number_format($transaction->balance_after) }}

                                    ریال

                                </td>


                                {{-- توضیح --}}
                                <td>

                                    {{ $transaction->description ?? '-' }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="text-muted py-4">

                                    تراکنشی ثبت نشده است.

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- صفحه‌بندی --}}
                <div class="mt-3">

                    {{ $transactions->links() }}

                </div>

            </div>

        </div>

    </div>

@endsection

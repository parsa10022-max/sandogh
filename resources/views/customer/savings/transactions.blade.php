@extends('layouts.app')

@section('title', 'گردش حساب پس‌انداز')

@section('content')

    <div class="container-fluid">

        {{-- خلاصه حساب --}}
        <div class="card shadow-sm mb-4">

            <div class="card-body">

                <div class="row align-items-center">

                    <div class="col-md-4">

                        <h5 class="mb-2">
                            حساب پس‌انداز
                        </h5>

                        <div class="text-muted">
                            شماره حساب:
                            <strong dir="ltr">
                                {{ $account->account_number }}
                            </strong>
                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="text-muted">
                            موجودی فعلی
                        </div>

                        <h4 class="text-success mb-0">

                            {{ number_format($account->balance) }}

                            <small>
                                ریال
                            </small>

                        </h4>

                    </div>


                    <div class="col-md-4 text-md-end">

                        <div class="text-muted">
                            تعداد تراکنش‌ها
                        </div>

                        <h4 class="mb-0">

                            {{ $transactions->total() }}

                        </h4>

                    </div>

                </div>

            </div>

        </div>



        {{-- جدول گردش حساب --}}
        <div class="card shadow-sm">

            <div class="card-header">

                <h5 class="mb-0">
                    گردش حساب
                </h5>

            </div>


            <div class="card-body p-0">


                <div class="table-responsive">


                    <table class="table table-hover align-middle mb-0">


                        <thead class="table-light">

                        <tr>

                            <th>
                                شماره تراکنش
                            </th>

                            <th>
                                تاریخ
                            </th>

                            <th>
                                نوع
                            </th>

                            <th class="text-center">
                                مبلغ
                            </th>

                            <th class="text-center">
                                مانده
                            </th>

                            <th>
                                توضیحات
                            </th>

                        </tr>

                        </thead>


                        <tbody>


                        @forelse($transactions as $transaction)


                            <tr>
                                <td>

    <span dir="ltr">

        {{ $transaction->transaction_no }}

    </span>

                                </td>

                                <td>

                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($transaction->transaction_date)
                                        ->format('Y/m/d') }}

                                </td>



                                <td>


                                    @if($transaction->transaction_type->value == 1)

                                        <span class="badge bg-success">

                                        <i class="bi bi-arrow-down-circle"></i>

                                        واریز

                                    </span>


                                    @else

                                        <span class="badge bg-danger">

                                        <i class="bi bi-arrow-up-circle"></i>

                                        برداشت

                                    </span>


                                    @endif


                                </td>



                                <td class="text-center">


                                    @if($transaction->transaction_type->value == 1)

                                        <span class="text-success fw-bold">

                                        +

                                        {{ number_format($transaction->amount) }}

                                    </span>


                                    @else

                                        <span class="text-danger fw-bold">

                                        -

                                        {{ number_format($transaction->amount) }}

                                    </span>


                                    @endif


                                    <small>
                                        ریال
                                    </small>


                                </td>



                                <td class="text-center fw-bold">

                                    {{ number_format($transaction->balance_after) }}

                                    <small>
                                        ریال
                                    </small>

                                </td>



                                <td>

                                    {{ $transaction->description ?? '-' }}

                                </td>


                            </tr>


                        @empty


                            <tr>

                                <td colspan="6"
                                    class="text-center text-muted py-4">

                                    تراکنشی ثبت نشده است.

                                </td>

                            </tr>


                        @endforelse


                        </tbody>


                    </table>


                </div>


            </div>


            <div class="card-footer">

                {{ $transactions->links() }}

            </div>


        </div>


    </div>


@endsection

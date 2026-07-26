@extends('receipts.layout')

@section('receipt-content')

    <div class="card border-0">

        <div class="card-body">

            <h5 class="text-primary fw-bold mb-4">

                اطلاعات پرداخت قسط

            </h5>

            <table class="table table-bordered align-middle">

                <tbody>

                <tr>
                    <th width="220">نام مشتری</th>
                    <td>{{ $payment->loan->customer->full_name }}</td>
                </tr>

                <tr>
                    <th>شماره وام</th>
                    <td>{{ $payment->loan->full_loan_number }}</td>
                </tr>

                <tr>
                    <th>شماره قسط</th>
                    <td>{{ $payment->installment->installment_number }}</td>
                </tr>

                <tr>
                    <th>مبلغ پرداخت</th>
                    <td class="fw-bold text-success">
                        {{ number_format($payment->amount) }} ریال
                    </td>
                </tr>

                <tr>
                    <th>تاریخ پرداخت</th>
                    <td>{{ $payment->paid_at_jalali }}</td>
                </tr>

                <tr>
                    <th>کد رهگیری</th>
                    <td>
                    <span class="font-monospace fw-bold">
                        {{ $payment->tracking_code }}
                    </span>
                    </td>
                </tr>

                <tr>
                    <th>شماره مرجع بانک</th>
                    <td>
                        {{ $payment->bank_reference_number ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <th>شناسه تراکنش بانک</th>
                    <td>
                        {{ $payment->bank_transaction_id ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <th>درگاه پرداخت</th>
                    <td>
                        {{ $payment->gateway }}
                    </td>
                </tr>

                <tr>
                    <th>اپراتور</th>
                    <td>
                        {{ $payment->user->name }}
                    </td>
                </tr>

                </tbody>

            </table>

        </div>

    </div>

@endsection

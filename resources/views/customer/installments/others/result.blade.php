@extends('layouts.app')

@section('title','اطلاعات قسط')

@section('content')

    <div class="container py-4">

        <div class="card shadow">

            <div class="card-header">

                <h5>
                    پرداخت قسط دیگران
                </h5>

            </div>


            <div class="card-body">


                <p>
                    <strong>نام عضو:</strong>

                    {{ $installment->loan->customer->full_name }}

                </p>


                <p>
                    <strong>شماره وام:</strong>

                    <span dir="ltr">
        {{ $installment->loan->full_loan_number }}
    </span>

                </p>


                <p>
                    <strong>نوع وام:</strong>

                    {{ $installment->loan->loanType->name }}

                </p>


                <p>
                    <strong>شماره قسط:</strong>

                    {{ $installment->installment_number }}

                </p>


                <p>
                    <strong>مبلغ:</strong>

                    {{ number_format($installment->amount) }}
                    ریال

                </p>


                <form method="POST"
                      action="{{ route('customer.installments.others.pay') }}">

                    @csrf

                    <input type="hidden"
                           name="installment_id"
                           value="{{ $installment->id }}">


                    <button class="btn btn-success">

                        پرداخت قسط

                    </button>


                </form>


            </div>

        </div>

    </div>

@endsection

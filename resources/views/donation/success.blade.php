@extends('layouts.app')

@section('title','پرداخت موفق کمک')

@section('content')

    <div class="container py-4">

        <div class="alert alert-success">

            <h4>
                پرداخت کمک با موفقیت انجام شد
            </h4>

            <p>
                مبلغ:
                {{ number_format($donationPayment->amount) }}
                ریال
            </p>

            <p>
                کد پیگیری:
                {{ $donationPayment->tracking_code }}
            </p>

        </div>

    </div>

@endsection

@extends('layouts.app')

@section('title','پرداخت موفق')

@section('content')

    <div class="container py-4">

        <div class="card shadow-sm">

            <div class="card-body text-center">

                <div class="text-success mb-3">

                    <i class="bi bi-check-circle-fill fs-1"></i>

                </div>


                <h4 class="mb-3">

                    پرداخت با موفقیت انجام شد

                </h4>


                <p>

                    مبلغ:

                    <strong>
                        {{ number_format($donationPayment->amount) }}
                        ریال
                    </strong>

                </p>


                <p>

                    کد رهگیری:

                    <strong>
                        {{ $donationPayment->tracking_code }}
                    </strong>

                </p>


                <a href="{{ route('customer.donations.create') }}"
                   class="btn btn-primary">

                    بازگشت

                </a>


            </div>

        </div>

    </div>

@endsection

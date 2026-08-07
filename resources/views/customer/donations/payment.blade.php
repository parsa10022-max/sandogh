@extends('layouts.app')

@section('title','پرداخت کمک')

@section('content')

    <div class="container py-4">


        <div class="card shadow-sm">


            <div class="card-header bg-primary text-white">

                <h5 class="mb-0">

                    <i class="bi bi-credit-card"></i>

                    پرداخت کمک

                </h5>

            </div>



            <div class="card-body">


                <div class="alert alert-info">

                    لطفاً اطلاعات پرداخت را بررسی کنید.

                </div>



                <div class="mb-3">

                    <strong>
                        حساب مقصد:
                    </strong>

                    {{ $donationPayment->account->name }}


                </div>



                <div class="mb-3">

                    <strong>
                        شماره حساب:
                    </strong>


                    <span dir="ltr">

                    {{ $donationPayment->account->account_number }}

                </span>


                </div>



                <div class="mb-4">

                    <strong>
                        مبلغ:
                    </strong>


                    {{ number_format($donationPayment->amount) }}

                    ریال


                </div>



                <form method="POST"
                      action="{{ route('customer.donations.pay',$donationPayment) }}">

                    @csrf

                    <button class="btn btn-success w-100">

                        <i class="bi bi-credit-card"></i>

                        پرداخت از طریق درگاه

                    </button>

                </form>



            </div>


        </div>


    </div>

@endsection

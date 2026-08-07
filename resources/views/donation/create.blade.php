@extends('layouts.app')

@section('title','کمک به صندوق')

@section('content')

    <div class="container py-4">

        <div class="card shadow-sm border">


            <div class="card-header bg-primary text-white">

                <h5 class="mb-0">

                    <i class="bi bi-heart-fill"></i>

                    کمک به صندوق

                </h5>

            </div>



            <div class="card-body">


                @if(session('success'))

                    <div class="alert alert-success">

                        {{ session('success') }}

                    </div>

                @endif



                @if($errors->any())

                    <div class="alert alert-danger">

                        <ul class="mb-0">

                            @foreach($errors->all() as $error)

                                <li>
                                    {{ $error }}
                                </li>

                            @endforeach

                        </ul>

                    </div>

                @endif



                <form method="POST"
                      action="{{ route('donation.store') }}">

                    @csrf



                    <div class="mb-4">


                        <label class="form-label fw-bold">

                            انتخاب حساب صندوق

                        </label>



                        <div class="row g-3">


                            @foreach($accounts as $account)


                                <div class="col-md-6">


                                    <input type="radio"
                                           class="btn-check"
                                           name="account_id"
                                           id="account{{ $account->id }}"
                                           value="{{ $account->id }}"
                                           required>



                                    <label class="card account-card h-100"
                                           for="account{{ $account->id }}">


                                        <div class="card-body">


                                            <div class="d-flex align-items-center">


                                                <div class="icon-box me-3">

                                                    <i class="bi bi-bank fs-3 text-primary"></i>

                                                </div>



                                                <div>


                                                    <h6 class="mb-1 fw-bold">

                                                        {{ $account->name }}

                                                    </h6>



                                                    <small class="text-muted">

                                                        شماره حساب:

                                                        {{ $account->account_number }}

                                                    </small>


                                                </div>


                                            </div>



                                            <div class="mt-3">


                                            <span class="badge bg-success">

                                                <i class="bi bi-check-circle"></i>

                                                فعال

                                            </span>


                                                <span class="selected-badge badge bg-primary d-none">

                                                انتخاب شد

                                            </span>


                                            </div>



                                        </div>


                                    </label>


                                </div>


                            @endforeach


                        </div>


                    </div>





                    <div class="mb-3">

                        <label class="form-label">

                            نام پرداخت کننده

                        </label>


                        <input type="text"
                               name="donor_name"
                               class="form-control">

                    </div>





                    <div class="mb-3">

                        <label class="form-label">

                            شماره موبایل

                        </label>


                        <input type="text"
                               name="donor_mobile"
                               class="form-control">

                    </div>





                    <div class="mb-3">

                        <label class="form-label">

                            مبلغ (ریال)

                        </label>


                        <input type="number"
                               name="amount"
                               class="form-control"
                               min="10000"
                               required>

                    </div>





                    <button class="btn btn-success w-100">

                        <i class="bi bi-credit-card"></i>

                        ادامه پرداخت

                    </button>



                </form>


            </div>

        </div>

    </div>





    <style>

        .account-card {

            cursor:pointer;

            transition:.2s;

            border:2px solid #dee2e6;

        }



        .account-card:hover {

            border-color:#0d6efd;

        }



        .btn-check:checked + .account-card {

            border-color:#198754;

            background:#f0fff5;

        }



        .btn-check:checked + .account-card .selected-badge {

            display:inline-block !important;

        }



        .icon-box {

            width:50px;

            height:50px;

            display:flex;

            align-items:center;

            justify-content:center;

            background:#eef6ff;

            border-radius:12px;

        }


    </style>


@endsection

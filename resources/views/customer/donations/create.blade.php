@extends('layouts.app')

@section('title','کمک به صندوق')

@section('content')

    <div class="container py-4">

        <div class="card shadow-sm border">

            <div class="card-header bg-primary text-white">

                <h5 class="mb-0">
                    <i class="bi bi-heart-fill"></i>
                    ثبت کمک
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
                      action="{{ route('customer.donations.store') }}">

                    @csrf



                    <label class="form-label fw-bold mb-3">

                        انتخاب حساب کمک

                    </label>



                    <div class="row g-3">


                        @foreach($accounts as $account)


                            <div class="col-md-6">


                                <label class="w-100">


                                    <input type="radio"
                                           name="account_id"
                                           value="{{ $account->id }}"
                                           class="btn-check account-radio"
                                           data-name="{{ $account->name }}"
                                           data-number="{{ $account->account_number }}"
                                           required>



                                    <div class="card account-card border-success">


                                        <div class="card-body">


                                            <div class="d-flex justify-content-between align-items-center">


                                                <div class="d-flex align-items-center">


                                                    <div class="icon-box me-3">

                                                        <i class="bi bi-bank fs-3 text-success"></i>

                                                    </div>



                                                    <div>


                                                        <h6 class="mb-1 fw-bold">

                                                            {{ $account->name }}

                                                        </h6>



                                                        <span dir="ltr"
                                                              class="text-muted">

                                                        {{ $account->account_number }}

                                                    </span>


                                                    </div>


                                                </div>



                                                <span class="badge bg-success status-active">

    <i class="bi bi-check-circle"></i>
    فعال

</span>


                                            </div>


                                        </div>


                                    </div>


                                </label>


                            </div>


                        @endforeach


                    </div>




                    <div class="mt-4">


                        <label class="form-label fw-bold">

                            مبلغ (ریال)

                        </label>


                        <input type="number"
                               name="amount"
                               class="form-control form-control-lg"
                               min="10000"
                               required>


                    </div>


                    <div id="selectedAccountBox"
                         class="alert alert-success d-none mt-4">

                        <div class="fw-bold mb-2">

                            <i class="bi bi-check-circle"></i>
                            حساب انتخاب شده

                        </div>


                        <div>

                            حساب:
                            <span id="selectedAccountName"></span>

                        </div>


                        <div dir="ltr">

                            شماره حساب:
                            <span id="selectedAccountNumber"></span>

                        </div>


                    </div>

                    <button class="btn btn-success w-100 mt-4">

                        <i class="bi bi-credit-card"></i>

                        ادامه پرداخت

                    </button>



                </form>


            </div>


        </div>


    </div>





    <script>
        document.querySelectorAll('.account-radio').forEach(input => {

            input.addEventListener('change', function () {


                document.querySelectorAll('.account-card')
                    .forEach(card => {

                        card.classList.remove('selected');

                    });


                this.closest('label')
                    .querySelector('.account-card')
                    .classList.add('selected');



                document.getElementById('selectedAccountBox')
                    .classList.remove('d-none');



                document.getElementById('selectedAccountName')
                    .innerText = this.dataset.name;



                document.getElementById('selectedAccountNumber')
                    .innerText = this.dataset.number;


            });

        });
    </script>

@endsection

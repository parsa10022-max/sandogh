@extends('layouts.app')

@section('title', 'درخواست برداشت')

@section('content')

    <div class="container">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-light">
                <h5 class="mb-0">
                    درخواست برداشت از حساب پس‌انداز
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



                {{-- اطلاعات حساب عضو --}}

                <div class="card bg-light border-0 mb-4">

                    <div class="card-body py-3">


                        <div class="row text-center">


                            <div class="col-md-3 border-end">

                                <small class="text-muted">
                                    عضو
                                </small>

                                <div class="fw-bold">

                                    {{ $account->customer->first_name }}

                                    {{ $account->customer->last_name }}

                                </div>

                            </div>



                            <div class="col-md-3 border-end">

                                <small class="text-muted">
                                    شماره حساب
                                </small>

                                <div class="fw-bold">

                                    {{ $account->account_number }}

                                </div>

                            </div>



                            <div class="col-md-3 border-end">

                                <small class="text-muted">
                                    بانک مقصد
                                </small>

                                <div class="fw-bold">

                                    {{ \App\Support\Iban::bankName($account->customer->iban) }}

                                </div>

                            </div>






                    </div>

                </div>




                    <form method="POST"
                          action="{{ route('accounts.withdrawal.store', $account) }}">

                        @csrf

                        {{-- موجودی --}}
                        <div class="alert alert-info">

                            موجودی قابل برداشت:

                            <strong>
                                {{ number_format($account->balance) }}
                                ریال
                            </strong>

                        </div>
                        <input
                            type="hidden"
                            id="availableBalance"
                            value="{{ $account->balance }}">

                        <div class="row">

                            <div class="col-md-12">

                                <x-inputs.iban-input
                                    name="iban"
                                    label="شماره شبا مقصد"
                                    :value="\App\Support\Iban::formatDigits($account->customer->iban)"
                                    live
                                    required
                                />

                                <div class="alert alert-info py-2 px-3 mt-2 mb-3 small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    شماره شبای فعلی شما نمایش داده شده است. در صورت تغییر، شماره شبای جدید را وارد کنید.
                                </div>

                            </div>

                        </div>

                        {{-- مبلغ برداشت --}}
                        <div class="mb-3">

                            <label class="form-label">
                                مبلغ برداشت
                            </label>

                            <input
                                type="number"
                                id="amount"
                                name="amount"
                                class="form-control @error('amount') is-invalid @enderror"
                                value="{{ old('amount') }}"
                                min="500000"
                                required>

                            <div
                                id="amountError"
                                class="invalid-feedback d-block"
                                style="display:none;">
                            </div>

                            @error('amount')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>

                        {{-- توضیحات --}}
                        <div class="mb-3">

                            <label class="form-label">
                                توضیحات
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="3">{{ old('description') }}</textarea>

                        </div>

                        <div class="text-end">

                            <button
                                id="submitBtn"
                                type="submit"
                                class="btn btn-danger">
                                ثبت درخواست برداشت
                            </button>

                        </div>

                    </form>


            </div>

        </div>


    </div>

        @push('scripts')

            <script>

                document.addEventListener('DOMContentLoaded', function () {

                    const amount = document.getElementById('amount');

                    const available = Number(
                        document.getElementById('availableBalance').value
                    );

                    const error = document.getElementById('amountError');

                    const submit = document.getElementById('submitBtn');

                    amount.addEventListener('input', function () {

                        const value = Number(this.value);

                        if (value > available) {

                            error.style.display = 'block';

                            error.innerHTML =
                                'مبلغ وارد شده از موجودی قابل برداشت بیشتر است.';

                            this.classList.add('is-invalid');

                            submit.disabled = true;

                        } else {

                            error.style.display = 'none';

                            error.innerHTML = '';

                            this.classList.remove('is-invalid');

                            submit.disabled = false;

                        }

                    });

                });

            </script>

    @endpush

@endsection

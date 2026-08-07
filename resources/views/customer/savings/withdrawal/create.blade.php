@extends('layouts.app')

@section('title', 'برداشت از حساب پس‌انداز')

@section('content')

    <div class="container py-4">

        <div class="row justify-content-center">

            <div class="col-lg-6">

                <div class="card shadow">

                    <div class="card-header">

                        <h5 class="mb-0">

                            برداشت از حساب پس‌انداز
                            <span class="text-primary fw-bold">
                            {{ $account->customer->full_name }}
                        </span>

                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="alert alert-info">

                            <div>

                                <strong>شماره حساب:</strong>

                                <span dir="ltr">

                                {{ $account->account_number }}

                            </span>

                            </div>

                            <div class="mt-2">

                                <strong>موجودی:</strong>

                                {{ number_format($account->balance) }}
                                ریال

                            </div>

                        </div>


                        <form
                            method="POST"
                            action="{{ route('customer.savings.withdrawal.store') }}">

                            @csrf


                            <div class="mb-3">

                                <label class="form-label">

                                    مبلغ برداشت

                                </label>

                                <input
                                    type="number"
                                    name="amount"
                                    min="500000"
                                    class="form-control"
                                    required>

                                @error('amount')

                                <div class="text-danger small mt-1">

                                    {{ $message }}

                                </div>

                                @enderror

                            </div>


                            <button
                                class="btn btn-danger">

                                ثبت درخواست برداشت

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection

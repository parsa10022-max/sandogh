@extends('layouts.app')

@section('title','پرداخت اقساط دیگران')

@section('content')

    <div class="container py-4">

        <div class="row justify-content-center">

            <div class="col-lg-6">

                <div class="card shadow">

                    <div class="card-header">

                        <h5 class="mb-0">

                            پرداخت اقساط دیگران

                        </h5>

                    </div>

                    <div class="card-body">

                        <form
                            method="POST"
                            action="{{ route('customer.installments.others.search') }}">

                            @csrf

                            <div class="mb-3">

                                <label class="form-label">

                                    شماره وام

                                </label>

                                <input
                                    type="text"
                                    name="loan_number"
                                    class="form-control"
                                    required>

                            </div>

                            <button
                                class="btn btn-primary">

                                جستجو

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection

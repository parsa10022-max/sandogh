@extends('layouts.app')

@section('title', 'ثبت وام')

@section('content')

    <div class="container-fluid">

        <div class="row justify-content-center">

            <div class="col-12">

                <div class="card shadow-sm border-0">

                    <div class="card-header bg-white">

                        <div class="d-flex justify-content-between align-items-center">

                            <h5 class="mb-0">
                                <i class="bi bi-cash-coin me-1"></i>
                                ثبت وام جدید
                            </h5>

                            <a href="{{ route('loans.index') }}"
                               class="btn btn-outline-secondary btn-sm">

                                <i class="bi bi-arrow-right"></i>

                                بازگشت

                            </a>

                        </div>

                    </div>

                    <div class="card-body">

                            <form
                                id="loan-form"
                                action="{{ route('loans.store') }}"
                                method="POST"
                                autocomplete="off"
                                data-calculate-url="{{ route('loans.calculate') }}">

                                @csrf

                                @include('loan._form')

                            </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection

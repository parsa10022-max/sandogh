@extends('layouts.app')

@section('title', 'ثبت وام')

@section('content')

    <div class="container-fluid loan-create-page">

        {{-- Page Header --}}
        <div class="loan-page-header">

            <div class="loan-page-header__content">

                <div class="loan-page-header__icon">
                    <i class="bi bi-cash-coin"></i>
                </div>

                <div>
                    <h1 class="loan-page-header__title">
                        ثبت وام جدید
                    </h1>

                    <p class="loan-page-header__subtitle mb-0">
                        اطلاعات وام، اقساط و ضامن‌ها را وارد کنید.
                    </p>
                </div>

            </div>

            <a href="{{ route('loans.index') }}"
               class="btn loan-back-btn">

                <i class="bi bi-arrow-right"></i>

                <span>بازگشت به لیست وام‌ها</span>

            </a>

        </div>

        {{-- Main Card --}}
        <div class="loan-form-card">

            <div class="loan-form-card__header">

                <div class="loan-form-card__header-icon">
                    <i class="bi bi-file-earmark-plus"></i>
                </div>

                <div>
                    <h2 class="loan-form-card__title">
                        اطلاعات وام
                    </h2>

                    <p class="loan-form-card__subtitle mb-0">
                        لطفاً اطلاعات را با دقت وارد کنید.
                    </p>
                </div>

            </div>


            <div class="loan-form-card__body">

                <form
                    id="loan-form"
                    action="{{ route('loans.store') }}"
                    method="POST"
                    autocomplete="off"
                    data-calculate-url="{{ route('loans.calculate') }}">

                    @csrf

                    @if(isset($loanRequest))

                        <input
                            type="hidden"
                            name="loan_request_id"
                            value="{{ $loanRequest->id }}"
                        >

                    @endif
                    @include('loan._form')

                </form>

            </div>

        </div>

    </div>
@endsection


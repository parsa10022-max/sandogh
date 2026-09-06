@extends('layouts.app')

@section('title', 'ویرایش وام')

@push('styles')
    @vite('resources/css/pages/loan-create.css')
@endpush

@section('content')

    <div class="container-fluid loan-create-page">

        {{-- =========================================================
             Page Header
        ========================================================== --}}

        <div class="loan-page-header">

            <div class="loan-page-header__content">

                <div class="loan-page-header__icon">
                    <i class="bi bi-pencil-square"></i>
                </div>

                <div>

                    <h1 class="loan-page-header__title">
                        ویرایش وام
                    </h1>

                    <p class="loan-page-header__subtitle mb-0">
                        اطلاعات وام، اقساط و ضامن‌ها را ویرایش کنید.
                    </p>

                </div>

            </div>


            <a
                href="{{ route('loans.show', $loan) }}"
                class="btn loan-back-btn">

                <i class="bi bi-arrow-right"></i>

                <span>
                    بازگشت به اطلاعات وام
                </span>

            </a>

        </div>


        {{-- =========================================================
             Main Form Card
        ========================================================== --}}

        <div class="loan-form-card">

            <div class="loan-form-card__header">

                <div class="loan-form-card__header-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>

                <div>

                    <h2 class="loan-form-card__title">
                        ویرایش اطلاعات وام
                    </h2>

                    <p class="loan-form-card__subtitle mb-0">

                        شماره وام:

                        <strong class="loan-number-display">
                            {{ $loan->full_loan_number }}
                        </strong>

                    </p>

                </div>

            </div>


            <div class="loan-form-card__body">

                <form
                    id="loan-form"
                    action="{{ route('loans.update', $loan) }}"
                    method="POST"
                    autocomplete="off"
                    data-calculate-url="{{ route('loans.calculate') }}">

                    @csrf
                    @method('PUT')

                    @include('loan._form')

                </form>

            </div>

        </div>

    </div>

@endsection

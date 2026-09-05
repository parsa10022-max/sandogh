@extends('layouts.app')

@section('title', 'ثبت مشتری جدید')

@section('content')

    <div class="container-fluid customer-create-page">

        {{-- =====================================================
             PAGE HEADER
        ====================================================== --}}

        <div class="customer-create-header">

            <div class="customer-create-header__content">

                <div class="customer-create-header__icon">
                    <i class="bi bi-person-plus"></i>
                </div>

                <div class="customer-create-header__text">

                    <h1 class="customer-create-title">
                        ثبت مشتری جدید
                    </h1>

                    <p class="customer-create-description">
                        اطلاعات مشتری جدید را وارد کنید.
                    </p>

                </div>

            </div>


            <a
                href="{{ route('customers.index') }}"
                class="btn customer-create-back-btn"
            >
                <i class="bi bi-arrow-right"></i>

                <span>
                    بازگشت به مشتریان
                </span>
            </a>

        </div>


        {{-- =====================================================
             FORM CARD
        ====================================================== --}}

        <div class="customer-create-card">

            <div class="customer-create-card__header">

                <div class="customer-create-card__title">

                    <div class="customer-create-card__icon">
                        <i class="bi bi-person-vcard"></i>
                    </div>

                    <div>

                        <h2>
                            اطلاعات مشتری
                        </h2>

                        <p>
                            مشخصات مشتری را با دقت وارد کنید.
                        </p>

                    </div>

                </div>

            </div>


            <div class="customer-create-card__body">

                <form
                    action="{{ route('customers.store') }}"
                    method="POST"
                    class="customer-create-form"
                >

                    @csrf

                    @include('customer._form')


                    {{-- =================================================
                         FORM ACTIONS
                    ================================================== --}}

                    <div class="customer-create-actions">

                        <a
                            href="{{ route('customers.index') }}"
                            class="btn customer-cancel-btn"
                        >
                            <i class="bi bi-x-lg"></i>

                            انصراف
                        </a>


                        <button
                            type="submit"
                            class="btn btn-primary customer-submit-btn"
                        >
                            <i class="bi bi-check-circle"></i>

                            ثبت مشتری
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection

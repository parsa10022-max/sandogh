@extends('layouts.app')

@section('title', 'ویرایش مشتری')

@section('content')

    <div class="container-fluid customer-edit-page">

        {{-- =====================================================
             PAGE HEADER
        ====================================================== --}}

        <div class="customer-edit-header">

            <div class="customer-edit-header__content">

                <div class="customer-edit-header__icon">
                    <i class="bi bi-pencil-square"></i>
                </div>

                <div class="customer-edit-header__text">

                    <h1 class="customer-edit-title">
                        ویرایش مشتری
                    </h1>

                    <p class="customer-edit-description">
                        اطلاعات مشتری را بررسی و ویرایش کنید.
                    </p>

                </div>

            </div>


            <a
                href="{{ route('customers.index') }}"
                class="btn customer-edit-back-btn"
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

        <div class="customer-edit-card">

            <div class="customer-edit-card__header">

                <div class="customer-edit-card__title">

                    <div class="customer-edit-card__icon">
                        <i class="bi bi-person-vcard"></i>
                    </div>

                    <div>

                        <h2>
                            اطلاعات مشتری
                        </h2>

                        <p>
                            مشخصات مشتری را با دقت بررسی و ویرایش کنید.
                        </p>

                    </div>

                </div>

            </div>


            <div class="customer-edit-card__body">

                <form
                    action="{{ route('customers.update', $customer) }}"
                    method="POST"
                    class="customer-edit-form"
                >

                    @csrf
                    @method('PUT')


                    @include('customer._form')


                    {{-- =================================================
                         FORM ACTIONS
                    ================================================== --}}

                    <div class="customer-edit-actions">

                        <a
                            href="{{ route('customers.index') }}"
                            class="btn customer-edit-cancel-btn"
                        >

                            <i class="bi bi-x-lg"></i>

                            <span>
                                انصراف
                            </span>

                        </a>


                        <button
                            type="submit"
                            class="btn customer-edit-submit-btn"
                        >

                            <i class="bi bi-check-circle"></i>

                            <span>
                                ذخیره تغییرات
                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection

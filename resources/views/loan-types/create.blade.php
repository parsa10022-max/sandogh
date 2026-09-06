@extends('layouts.app')

@section('title', 'ثبت نوع وام')

@section('content')

    <div class="container-fluid loan-type-create-page">

        {{-- Page Header --}}
        <x-page-header title="ثبت نوع وام">

            <a
                href="{{ route('loan-types.index') }}"
                class="btn loan-type-back-btn"
            >
                <i class="bi bi-arrow-right"></i>
                <span>بازگشت به انواع وام</span>
            </a>

        </x-page-header>


        <form
            action="{{ route('loan-types.store') }}"
            method="POST"
        >

            @csrf

            {{-- Form Card --}}
            <div class="loan-type-form-card">

                <div class="loan-type-form-header">

                    <div class="loan-type-form-header-icon">
                        <i class="bi bi-plus-lg"></i>
                    </div>

                    <div>
                        <h5>اطلاعات نوع وام</h5>

                        <p>
                            مشخصات نوع وام جدید را وارد کنید.
                        </p>
                    </div>

                </div>


                <div class="loan-type-form-body">

                    <div class="row g-3">

                        {{-- Name --}}
                        <div class="col-12 col-md-6">

                            <x-inputs.text-input
                                name="name"
                                label="نام نوع وام"
                                :value="old('name')"
                                required
                            />

                        </div>


                        {{-- Prefix --}}
                        <div class="col-12 col-md-6">

                            <x-inputs.text-input
                                name="prefix"
                                label="پیش‌شماره وام"
                                :value="old('prefix')"
                                required
                            />

                        </div>


                        {{-- Description --}}
                        <div class="col-12">

                            <x-inputs.textarea-input
                                name="description"
                                label="توضیحات"
                                :value="old('description')"
                            />

                        </div>


                        {{-- Status --}}
                        <div class="col-12 col-md-6">

                            <x-inputs.select-input
                                name="status"
                                label="وضعیت"
                                :options="\App\Enums\LoanTypeStatus::options()"
                                :value="old(
                                    'status',
                                    \App\Enums\LoanTypeStatus::ACTIVE->value
                                )"
                                required
                            />

                        </div>

                    </div>

                </div>


                {{-- Actions --}}
                <div class="loan-type-form-footer">

                    <button
                        type="submit"
                        class="btn btn-primary loan-type-submit-btn"
                    >
                        <i class="bi bi-check-lg"></i>
                        <span>ثبت نوع وام</span>
                    </button>


                    <a
                        href="{{ route('loan-types.index') }}"
                        class="btn loan-type-cancel-btn"
                    >
                        <i class="bi bi-x-lg"></i>
                        <span>انصراف</span>
                    </a>

                </div>

            </div>

        </form>

    </div>

@endsection

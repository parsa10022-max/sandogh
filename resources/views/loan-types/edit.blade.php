@extends('layouts.app')

@section('title', 'ویرایش نوع وام')

@section('content')

    <div class="container-fluid loan-type-edit-page">

        {{-- Page Header --}}
        <x-page-header title="ویرایش نوع وام">

            <a
                href="{{ route('loan-types.index') }}"
                class="btn loan-type-back-btn"
            >
                <i class="bi bi-arrow-right"></i>
                <span>بازگشت به انواع وام</span>
            </a>

        </x-page-header>

        <form
            action="{{ route('loan-types.update', $loanType) }}"
            method="POST"
        >

            @csrf
            @method('PUT')

            {{-- Form Card --}}
            <div class="loan-type-form-card">

                <div class="loan-type-form-header">

                    <div class="loan-type-form-header-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>

                    <div>
                        <h5>ویرایش نوع وام</h5>

                        <p>
                            اطلاعات نوع وام را ویرایش و ذخیره کنید.
                        </p>
                    </div>

                </div>

                <div class="loan-type-form-body">

                    @include('loan-types._form')

                </div>

            </div>

        </form>

    </div>

@endsection


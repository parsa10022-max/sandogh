@extends('customer.layouts.app')

@section('title', 'پروفایل')

@section('content')

    <div class="container-fluid px-0">

        <div class="mb-4">
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-person-circle me-1"></i>
                پروفایل من
            </h1>
            <p class="text-muted mb-0">
                اطلاعات حساب و عضویت شما
            </p>
        </div>

        <div class="card border rounded-4 shadow-sm">
            <div class="card-body p-4">

                <div class="text-center mb-4">
                    <div class="rounded-circle bg-light d-inline-flex
                            align-items-center justify-content-center"
                         style="width:80px;height:80px;">
                        <i class="bi bi-person fs-1"></i>
                    </div>

                    <h2 class="h5 fw-bold mt-3 mb-1">
                        {{ $customer?->full_name ?? 'مشتری محترم' }}
                    </h2>

                    <small class="text-muted">
                        کد عضویت: {{ $customer?->customer_code ?? '—' }}
                    </small>
                </div>

                <div class="row g-3">

                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted">نام</label>
                        <div class="form-control bg-light">
                            {{ $customer?->first_name ?? '—' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted">نام خانوادگی</label>
                        <div class="form-control bg-light">
                            {{ $customer?->last_name ?? '—' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted">نام پدر</label>
                        <div class="form-control bg-light">
                            {{ $customer?->father_name ?? '—' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted">شماره موبایل</label>
                        <div class="form-control bg-light">
                            {{ $customer?->mobile ?? $user->mobile ?? '—' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted">کد ملی</label>
                        <div class="form-control bg-light">
                            {{ $customer?->national_code ?? '—' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted">شماره شبا</label>
                        <div class="form-control bg-light">
                            {{ $customer?->iban ?? '—' }}
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

@endsection


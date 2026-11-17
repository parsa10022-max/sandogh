@extends('layouts.app')

@section('title', 'ویرایش مشتری')

@section('content')

    <div class="container-fluid customer-edit-page">

        {{-- PAGE HEADER --}}

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
                <span>بازگشت به مشتریان</span>
            </a>

        </div>


        {{-- CUSTOMER INFORMATION FORM --}}

        <div class="customer-edit-card">

            <div class="customer-edit-card__header">

                <div class="customer-edit-card__title">

                    <div class="customer-edit-card__icon">
                        <i class="bi bi-person-vcard"></i>
                    </div>

                    <div>

                        <h2>اطلاعات مشتری</h2>

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

                    <div class="customer-edit-actions">

                        <a
                            href="{{ route('customers.index') }}"
                            class="btn customer-edit-cancel-btn"
                        >
                            <i class="bi bi-x-lg"></i>
                            <span>انصراف</span>
                        </a>

                        <button
                            type="submit"
                            class="btn customer-edit-submit-btn"
                        >
                            <i class="bi bi-check-circle"></i>
                            <span>ذخیره تغییرات</span>
                        </button>

                    </div>

                </form>

            </div>

        </div>


        {{-- ONLINE ACTIVATION APPROVAL --}}

        <div class="customer-edit-card mt-4">

            <div class="customer-edit-card__header">

                <div class="customer-edit-card__title">

                    <div class="customer-edit-card__icon">
                        <i class="bi bi-shield-check"></i>
                    </div>

                    <div>

                        <h2>مجوز فعال‌سازی اینترنتی</h2>

                        <p>
                            اجازه ساخت حساب کاربری اینترنتی برای این عضو
                        </p>

                    </div>

                </div>

            </div>

            <div class="customer-edit-card__body">

                <div class="d-flex flex-column flex-md-row
                            justify-content-between align-items-md-center gap-3">

                    <div>

                        @if($customer->online_activation_approved)

                            <span class="badge bg-success fs-6">
                                <i class="bi bi-check-circle me-1"></i>
                                تأیید شده
                            </span>

                            <p class="text-muted mt-2 mb-0">
                                این عضو مجاز به دریافت کد تأیید و ساخت حساب کاربری اینترنتی است.
                            </p>

                        @else

                            <span class="badge bg-secondary fs-6">
                                <i class="bi bi-hourglass-split me-1"></i>
                                تأیید نشده
                            </span>

                            <p class="text-muted mt-2 mb-0">
                                این عضو تا زمان تأیید اپراتور امکان ساخت حساب کاربری اینترنتی ندارد.
                            </p>

                        @endif

                    </div>


                    @if($canManageOnlineActivation)

                        <form
                            action="{{ route('customers.online-activation.update', $customer) }}"
                            method="POST"
                            class="d-flex gap-2 flex-wrap"
                        >

                            @csrf
                            @method('PATCH')

                            @if($customer->online_activation_approved)

                                <button
                                    type="submit"
                                    name="approved"
                                    value="0"
                                    class="btn btn-outline-danger"
                                    onclick="return confirm('آیا از لغو مجوز فعال‌سازی اینترنتی این عضو اطمینان دارید؟')"
                                >
                                    <i class="bi bi-x-circle me-1"></i>
                                    لغو تأیید
                                </button>

                            @else

                                <button
                                    type="submit"
                                    name="approved"
                                    value="1"
                                    class="btn btn-success"
                                    onclick="return confirm('آیا هویت این عضو را بررسی کرده‌اید و تأیید فعال‌سازی اینترنتی را می‌پذیرید؟')"
                                >
                                    <i class="bi bi-check-circle me-1"></i>
                                    تأیید فعال‌سازی اینترنتی
                                </button>

                            @endif

                        </form>

                    @else

                        <span class="text-muted small">
                            امکان تغییر مجوز برای نقش کاربری شما وجود ندارد.
                        </span>

                    @endif

                </div>

            </div>

        </div>

    </div>

@endsection

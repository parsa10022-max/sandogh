@extends('layouts.app')

@section('title', 'مدیریت مشتریان')

@section('content')

    <div class="container-fluid customer-index-page">

        {{-- Header --}}
        <div class="customer-index-header">

            <div class="customer-index-header__content">

                <div class="customer-index-header__icon">
                    <i class="bi bi-people"></i>
                </div>

                <div class="customer-index-header__text">

                    <h1 class="customer-index-title">
                        آرشیو مشتریان
                    </h1>

                    <p class="customer-index-description">
                        مدیریت، مشاهده و بازگردانی مشتریان
                    </p>

                </div>

            </div>

        </div>


        {{-- Search --}}
        <div class="customer-index-search-card">

            <div class="customer-index-search-card__header">

                <div class="customer-index-search-card__title">

                    <div class="customer-index-search-card__icon">
                        <i class="bi bi-search"></i>
                    </div>

                    <div>
                        <h2>جستجوی مشتری</h2>
                        <p>بر اساس اطلاعات مشتری جستجو کنید.</p>
                    </div>

                </div>

            </div>

            <div class="customer-index-search-card__body">

                <form
                    method="GET"
                    action="{{ route('customers.index') }}"
                    class="customer-index-search-form"
                >

                    <div class="customer-index-search-input">

                        <label for="customer-search" class="form-label">
                            جستجو
                        </label>

                        <input
                            id="customer-search"
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="نام، کد مشتری، کد ملی یا موبایل..."
                            value="{{ request('search') }}"
                            autocomplete="off"
                        >

                    </div>

                    <div class="customer-index-search-actions">

                        <button
                            type="submit"
                            class="btn customer-index-search-btn"
                        >
                            <i class="bi bi-search"></i>
                            <span>جستجو</span>
                        </button>

                        @if(request()->filled('search'))

                            <a
                                href="{{ route('customers.index') }}"
                                class="btn customer-index-clear-btn"
                            >
                                <i class="bi bi-x-circle"></i>
                                <span>پاک کردن</span>
                            </a>

                        @endif

                    </div>

                </form>

            </div>

        </div>


        {{-- Customers Table --}}
        <div class="customer-index-table-card">

            <div class="customer-index-table-card__header">

                <div class="customer-index-table-card__title">

                    <div class="customer-index-table-card__icon">
                        <i class="bi bi-people"></i>
                    </div>

                    <div>
                        <h2>لیست مشتریان</h2>
                        <p>مشتریان موجود در آرشیو صندوق</p>
                    </div>

                </div>

            </div>

            <div class="customer-index-table-wrapper">

                <table class="table customer-index-table align-middle mb-0">

                    <thead>

                    <tr>
                        <th>کد</th>
                        <th>نام</th>
                        <th>کد ملی</th>
                        <th>موبایل</th>
                        <th>وضعیت</th>
                        <th class="customer-index-actions-column">عملیات</th>
                    </tr>

                    </thead>

                    <tbody>

                    @forelse($customers as $customer)

                        <tr>

                            <td>
                                <span class="customer-index-code">
                                    {{ $customer->customer_code }}
                                </span>
                            </td>

                            <td>
                                <span class="customer-index-name">
                                    {{ $customer->full_name }}
                                </span>
                            </td>

                            <td >
                                {{ $customer->national_code }}
                            </td>

                            <td >
                                {{ $customer->mobile }}
                            </td>

                            <td>

                                <span class="customer-index-status">
                                    {{ $customer->status->label() }}
                                </span>

                            </td>

                            <td class="text-center">

                                <div class="customer-index-actions">



                                    <form
                                        action="{{ route('customers.restore', $customer->id) }}"
                                        method="POST"
                                        class="d-inline"
                                    >

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn customer-index-action-btn customer-index-action-btn--restore"
                                            title="بازگردانی"
                                        >
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="customer-index-empty"
                            >

                                <div class="customer-index-empty__icon">
                                    <i class="bi bi-people"></i>
                                </div>

                                <div class="customer-index-empty__text">
                                    مشتری ثبت نشده است.
                                </div>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- Pagination --}}
        @if($customers->hasPages())

            <div class="customer-index-pagination">
                {{ $customers->links() }}
            </div>

        @endif

    </div>

@endsection

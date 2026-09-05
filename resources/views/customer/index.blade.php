@extends('layouts.app')

@section('title', 'مدیریت مشتریان')

@section('content')

    <div class="container-fluid customers-page">

        {{-- =====================================================
             PAGE HEADER
        ====================================================== --}}

        <div class="customers-page-header">

            <x-page-header title="مدیریت مشتریان">

                <a
                    href="{{ route('customers.create') }}"
                    class="btn btn-primary customers-add-btn"
                >
                    <i class="bi bi-plus-circle"></i>
                    <span>مشتری جدید</span>
                </a>

            </x-page-header>

        </div>


        {{-- =====================================================
             SEARCH
        ====================================================== --}}

        <div class="customers-search">

            <x-search-box
                :action="route('customers.index')"
            />

        </div>


        {{-- =====================================================
             TABLE
        ====================================================== --}}

        <x-datatable.table>

            <div class="customers-table-wrapper">

                <table class="table customers-table mb-0">

                    <thead>

                    <tr>
                        <th>کد</th>
                        <th>نام</th>
                        <th>کد ملی</th>
                        <th>موبایل</th>
                        <th>وضعیت</th>
                        <th class="customers-actions-column">
                            عملیات
                        </th>
                    </tr>

                    </thead>

                    <tbody>

                    @forelse($customers as $customer)

                        <tr>

                            <td data-label="کد">
                                <span class="customer-code">
                                    {{ $customer->customer_code }}
                                </span>
                            </td>

                            <td data-label="نام">
                                <span class="customer-name">
                                    {{ $customer->full_name }}
                                </span>
                            </td>

                            <td data-label="کد ملی">
                                {{ $customer->national_code }}
                            </td>

                            <td data-label="موبایل">
                                {{ $customer->mobile }}
                            </td>

                            <td data-label="وضعیت">

                                <span class="customer-status">
                                    {{ $customer->status->label() }}
                                </span>

                            </td>

                            <td
                                data-label="عملیات"
                                class="customers-actions"
                            >

                                <div class="d-flex justify-content-center gap-1">

                                    <x-action-buttons
                                        :show-route="route('customers.show', $customer)"
                                        :edit-route="route('customers.edit', $customer)"
                                        :delete-route="route('customers.destroy', $customer)"
                                    />

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="customers-empty"
                            >

                                <div class="customers-empty-content">

                                    <i class="bi bi-people"></i>

                                    <span>
                                        مشتری ثبت نشده است.
                                    </span>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </x-datatable.table>


        {{-- =====================================================
             PAGINATION
        ====================================================== --}}

        <x-pagination :items="$customers"/>

    </div>

@endsection


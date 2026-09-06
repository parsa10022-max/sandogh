@extends('layouts.app')

@section('title', 'مدیریت انواع وام')

@section('content')

    <div class="container-fluid loan-types-page">

        {{-- Page Header --}}
        <x-page-header title="مدیریت انواع وام">

            <a
                href="{{ route('loan-types.create') }}"
                class="btn btn-primary loan-type-create-btn"
            >
                <i class="bi bi-plus-lg"></i>
                <span>نوع وام جدید</span>
            </a>

        </x-page-header>


        {{-- Search --}}
        <div class="loan-types-search mb-4">

            <x-search-box
                :action="route('loan-types.index')"
            />

        </div>


        {{-- Main Card --}}
        <x-datatable.table>

            <div class="loan-types-card">

                {{-- Card Header --}}
                <div class="loan-types-card-header">

                    <div class="loan-types-title">

                        <div class="loan-types-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>

                        <div>

                            <h6 class="mb-1">
                                انواع وام
                            </h6>

                            <small>
                                مدیریت و وضعیت انواع وام‌های صندوق
                            </small>

                        </div>

                    </div>


                    <div class="loan-types-count">

                        <span>
                            تعداد
                        </span>

                        <strong>
                            {{ $loanTypes->total() }}
                        </strong>

                    </div>

                </div>


                {{-- Table --}}
                <div class="table-responsive">

                    <table class="table loan-types-table align-middle mb-0">

                        <thead>

                        <tr>

                            <th>
                                نام نوع وام
                            </th>

                            <th>
                                پیش‌شماره
                            </th>

                            <th>
                                وضعیت
                            </th>

                            <th class="text-center actions-column">
                                عملیات
                            </th>

                        </tr>

                        </thead>


                        <tbody>

                        @forelse($loanTypes as $loanType)

                            <tr>

                                {{-- Loan Name --}}
                                <td>

                                    <div class="loan-type-name">

                                        <div class="loan-type-small-icon">
                                            <i class="bi bi-credit-card-2-front"></i>
                                        </div>

                                        <div>

                                            <div class="fw-bold">
                                                {{ $loanType->name }}
                                            </div>

                                            @if($loanType->description)

                                                <small>
                                                    {{ $loanType->description }}
                                                </small>

                                            @endif

                                        </div>

                                    </div>

                                </td>


                                {{-- Prefix --}}
                                <td>

                                    <span class="loan-prefix">
                                        {{ $loanType->prefix }}
                                    </span>

                                </td>


                                {{-- Status --}}
                                <td>

                                    <span class="loan-status loan-status-{{ $loanType->status->value }}">

                                        <span class="loan-status-dot"></span>

                                        {{ $loanType->status->label() }}

                                    </span>

                                </td>


                                {{-- Actions --}}
                                <td class="text-center">

                                    <div class="loan-type-actions">

                                        <x-action-buttons
                                            :edit-route="route('loan-types.edit', $loanType)"
                                            :change-status-route="route('loan-types.change-status', $loanType)"
                                        />

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="4">

                                    <div class="loan-types-empty">

                                        <div class="empty-icon">
                                            <i class="bi bi-wallet2"></i>
                                        </div>

                                        <h6>
                                            نوع وامی ثبت نشده است
                                        </h6>

                                        <p>
                                            هنوز هیچ نوع وامی در صندوق ثبت نشده است.
                                        </p>

                                        <a
                                            href="{{ route('loan-types.create') }}"
                                            class="btn btn-primary"
                                        >
                                            <i class="bi bi-plus-lg"></i>
                                            ثبت نوع وام
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </x-datatable.table>


        {{-- Pagination --}}
        <div class="mt-4">

            <x-pagination :items="$loanTypes"/>

        </div>

    </div>

@endsection


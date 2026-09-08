@extends('layouts.app')

@section('title', 'حساب‌های سیستمی')

@push('styles')
    @vite('resources/css/admin/system-accounts/index.css')
@endpush

@section('content')

    <div class="container-fluid system-accounts-page">

        {{-- Header --}}
        <div class="system-accounts-header">

            <div class="system-accounts-header__content">

                <div class="system-accounts-header__icon">
                    <i class="bi bi-bank"></i>
                </div>

                <div>
                    <h1 class="system-accounts-header__title">
                        حساب‌های سیستمی
                    </h1>

                    <p class="system-accounts-header__subtitle mb-0">
                        مدیریت حساب‌های مالی و سیستمی صندوق
                    </p>
                </div>

            </div>

            <a href="{{ route('system-accounts.create') }}"
               class="system-accounts-create-btn">

                <i class="bi bi-plus-circle"></i>

                <span>
                    ایجاد حساب جدید
                </span>

            </a>

        </div>


        {{-- Accounts Card --}}
        <div class="system-accounts-card">

            <div class="system-accounts-card__header">

                <div class="system-accounts-card__title-wrapper">

                    <div class="system-accounts-card__icon">
                        <i class="bi bi-wallet2"></i>
                    </div>

                    <div>
                        <h2 class="system-accounts-card__title">
                            فهرست حساب‌های سیستمی
                        </h2>

                        <p class="system-accounts-card__subtitle mb-0">
                            حساب‌های ثبت‌شده در سیستم
                        </p>
                    </div>

                </div>

            </div>


            {{-- Table --}}
            <div class="system-accounts-card__body">

                <div class="table-responsive">

                    <table class="table system-accounts-table align-middle mb-0">

                        <thead>

                        <tr>

                            <th class="system-accounts-table__index">
                                #
                            </th>

                            <th>
                                نام حساب
                            </th>

                            <th>
                                شماره حساب
                            </th>

                            <th>
                                موجودی
                            </th>

                            <th class="text-center">
                                وضعیت
                            </th>

                            <th class="text-center system-accounts-table__actions">
                                عملیات
                            </th>

                        </tr>

                        </thead>

                        <tbody>

                        @forelse($accounts as $account)

                            <tr>

                                <td>

                                    <span class="system-accounts-row-number">
                                        {{ $loop->iteration }}
                                    </span>

                                </td>


                                <td>

                                    <div class="system-account-name">

                                        <div class="system-account-name__icon">
                                            <i class="bi bi-wallet2"></i>
                                        </div>

                                        <span>
                                            {{ $account->name }}
                                        </span>

                                    </div>

                                </td>


                                <td>

                                    <span class="system-account-number">
                                        {{ $account->account_number }}
                                    </span>

                                </td>


                                <td>

                                    <div class="system-account-balance">

                                        <strong>
                                            {{ number_format($account->balance) }}
                                        </strong>

                                        <small>
                                            ریال
                                        </small>

                                    </div>

                                </td>


                                <td class="text-center">

                                    @if($account->status === \App\Enums\AccountStatus::ACTIVE)

                                        <span class="system-account-status system-account-status--active">

                                            <span class="system-account-status__dot"></span>

                                            فعال

                                        </span>

                                    @else

                                        <span class="system-account-status system-account-status--inactive">

                                            <span class="system-account-status__dot"></span>

                                            غیرفعال

                                        </span>

                                    @endif

                                </td>


                                <td class="text-center">

                                    <div class="system-account-actions">

                                        <a href="{{ route('system-accounts.edit', $account) }}"
                                           class="system-account-action system-account-action--edit">

                                            <i class="bi bi-pencil"></i>

                                            <span>
                                                ویرایش
                                            </span>

                                        </a>


                                        <form method="POST"
                                              action="{{ route('system-accounts.change-status', $account) }}"
                                              class="d-inline">

                                            @csrf
                                            @method('PATCH')

                                            @if($account->status === \App\Enums\AccountStatus::ACTIVE)

                                                <button type="submit"
                                                        class="system-account-action system-account-action--disable">

                                                    <i class="bi bi-lock"></i>

                                                    <span>
                                                        غیرفعال
                                                    </span>

                                                </button>

                                            @else

                                                <button type="submit"
                                                        class="system-account-action system-account-action--enable">

                                                    <i class="bi bi-unlock"></i>

                                                    <span>
                                                        فعال
                                                    </span>

                                                </button>

                                            @endif

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6">

                                    <div class="system-accounts-empty">

                                        <div class="system-accounts-empty__icon">
                                            <i class="bi bi-wallet2"></i>
                                        </div>

                                        <strong>
                                            حساب سیستمی ثبت نشده است
                                        </strong>

                                        <span>
                                            هنوز هیچ حساب سیستمی در سیستم ایجاد نشده است.
                                        </span>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>


            @if($accounts->hasPages())

                <div class="system-accounts-card__footer">

                    {{ $accounts->links() }}

                </div>

            @endif

        </div>

    </div>

@endsection

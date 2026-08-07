@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5 class="fw-bold mb-0">
                حساب‌های سیستمی
            </h5>

            <a href="{{ route('system-accounts.create') }}"
               class="btn btn-primary">

                <i class="bi bi-plus-circle"></i>
                ایجاد حساب جدید

            </a>

        </div>


        <div class="card">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead class="table-light">

                        <tr>

                            <th>#</th>

                            <th>نام حساب</th>

                            <th>شماره حساب</th>

                            <th>موجودی</th>

                            <th>وضعیت</th>

                            <th width="220">
                                عملیات
                            </th>

                        </tr>

                        </thead>

                        <tbody>

                        @forelse($accounts as $account)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    {{ $account->name }}
                                </td>

                                <td>
                                    {{ $account->account_number }}
                                </td>

                                <td>
                                    {{ number_format($account->balance) }}
                                </td>

                                <td>

                                <span class="badge
                                    {{ $account->status === \App\Enums\AccountStatus::ACTIVE
                                        ? 'bg-success'
                                        : 'bg-secondary' }}">

                                    {{ $account->status->label() }}

                                </span>

                                </td>

                                <td>

                                    <a href="{{ route(
                                    'system-accounts.edit',
                                    $account
                                ) }}"
                                       class="btn btn-sm btn-warning">

                                        <i class="bi bi-pencil"></i>
                                        ویرایش

                                    </a>


                                    <form method="POST"
                                          action="{{ route(
                                        'system-accounts.change-status',
                                        $account
                                      ) }}"
                                          class="d-inline">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="btn btn-sm
                                            {{ $account->status === \App\Enums\AccountStatus::ACTIVE
                                                ? 'btn-danger'
                                                : 'btn-success' }}">

                                            @if($account->status === \App\Enums\AccountStatus::ACTIVE)

                                                <i class="bi bi-lock"></i>
                                                غیرفعال

                                            @else

                                                <i class="bi bi-unlock"></i>
                                                فعال

                                            @endif

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6"
                                    class="text-center py-4">

                                    حساب سیستمی ثبت نشده است.

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

            <div class="card-footer">

                {{ $accounts->links() }}

            </div>

        </div>

    </div>

@endsection

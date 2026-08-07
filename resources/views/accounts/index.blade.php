@extends('layouts.app')

@section('title','حساب‌ها')

@section('content')
    <div class="card shadow-sm border-2 mb-3">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3"
                 dir="rtl">


                {{-- جستجو --}}
                <div style="width:350px">

                    <form method="GET">

                        <label class="form-label small fw-bold mb-1">
                            <i class="bi bi-search"></i>
                            جستجوی حساب
                        </label>


                        <div class="input-group">

                            <input
                                type="text"
                                name="search"
                                class="form-control text-end"
                                placeholder="شماره حساب، نام، کد ملی..."
                                value="{{ request('search') }}"
                                style="height:42px"
                            >


                            <button class="btn btn-primary">

                                <i class="bi bi-search"></i>

                            </button>


                        </div>

                    </form>

                </div>



                {{-- آمار --}}
                <div class="d-flex gap-3">


                    <div class="border border-2 rounded p-3 bg-light text-center"
                         style="min-width:150px">

                        <div class="small text-muted">

                            <i class="bi bi-wallet2"></i>
                            تعداد حساب‌ها

                        </div>


                        <div class="fw-bold fs-5">

                            {{ number_format($totalAccounts) }}

                        </div>

                    </div>



                    <div class="border border-2 rounded p-3 bg-light text-center"
                         style="min-width:220px">

                        <div class="small text-muted">

                            <i class="bi bi-cash-stack"></i>
                            موجودی کل

                        </div>


                        <div class="fw-bold fs-5">

                            {{ number_format($totalBalance) }}
                            ریال

                        </div>

                    </div>


                </div>


            </div>


        </div>

    </div>
    <div class="container">

        <div class="card shadow-sm">

            <div class="card-header bg-light">
                <h5 class="mb-0">
                    لیست حساب‌ها
                </h5>
            </div>


            <div class="card-body">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                    <tr>
                        <th>شماره حساب</th>
                        <th>کد مشتری</th>
                        <th>نام حساب / صاحب حساب</th>

                        <th>موجودی</th>
                        <th>وضعیت</th>
                        <th width="90">عملیات</th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach($accounts as $account)

                        <tr class="{{ $account->customer ? '' : 'table-primary' }}">

                            {{-- شماره حساب --}}
                            <td>

                                @if($account->customer)

                                    <i class="bi bi-person-fill text-success me-1"></i>

                                @else

                                    <i class="bi bi-bank2 text-primary me-1"></i>

                                @endif

                                <strong>{{ $account->account_number }}</strong>

                            </td>


                            {{-- کد مشتری --}}
                            <td class="text-center">

                                {{ $account->customer?->customer_code ?? '-' }}

                            </td>


                            {{-- نام --}}
                            <td>

                                @if($account->customer)
                                    <span class="badge bg-success ms-2">
                                        مشتری
                                      </span>

                                    {{ $account->customer->first_name }}
                                    {{ $account->customer->last_name }}



                                @else
                                    <span class="badge bg-primary ms-2">
                                          سیستمی
                                      </span>
                                    <strong>{{ $account->name }}</strong>



                                @endif

                            </td>


                            {{-- نوع حساب --}}



                            {{-- موجودی --}}
                            <td class="text-start">

                                <strong>

                                    {{ number_format($account->balance) }}

                                </strong>

                                ریال

                            </td>


                            {{-- وضعیت --}}
                            <td>

                                @if($account->status === \App\Enums\AccountStatus::ACTIVE)

                                    <span class="badge bg-success">
            <i class="bi bi-check-circle"></i>
            فعال
        </span>

                                @else

                                    <span class="badge bg-danger">
            <i class="bi bi-x-circle"></i>
            غیرفعال
        </span>

                                @endif

                            </td>


                            {{-- عملیات --}}
                            <td class="text-center">

                                <a href="{{ route('accounts.show',$account) }}"
                                   class="btn btn-outline-primary btn-sm">

                                    <i class="bi bi-eye"></i>

                                </a>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>


                {{ $accounts->links() }}

            </div>

        </div>

    </div>

@endsection

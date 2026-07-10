@extends('layouts.app')

@section('title', 'مدیریت مشتریان')

@section('content')

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h3>

                مدیریت مشتریان

            </h3>

            <a
                href="{{ route('customers.create') }}"
                class="btn btn-primary"
            >

                <i class="bi bi-plus-circle"></i>

                مشتری جدید

            </a>

        </div>

        {{-- Search --}}
        <div class="card mb-3">

            <div class="card-body">

                <form method="GET"
                      action="{{ route('customers.index') }}">

                    <div class="row">

                        <div class="col-md-4">

                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="جستجو..."

                                value="{{ request('search') }}">

                        </div>

                        <div class="col-auto">

                            <button
                                class="btn btn-primary">

                                <i class="bi bi-search"></i>

                                جستجو

                            </button>

                        </div>

                        @if(request()->filled('search'))

                            <div class="col-auto">

                                <a href="{{ route('customers.index') }}"
                                   class="btn btn-secondary">

                                    پاک کردن

                                </a>

                            </div>

                        @endif

                    </div>

                </form>

            </div>

        </div>

        {{-- Table --}}
        <div class="card shadow-sm">

            <div class="card-body p-0">

                <table class="table table-hover table-striped align-middle mb-0">

                    <thead class="table-dark">

                    <tr>

                        <th>کد</th>

                        <th>نام</th>

                        <th>کد ملی</th>

                        <th>موبایل</th>

                        <th>وضعیت</th>

                        <th width="120">عملیات</th>

                    </tr>

                    </thead>

                    <tbody>

                    @forelse($customers as $customer)

                        <tr>

                            <td>{{ $customer->customer_code }}</td>

                            <td>{{ $customer->full_name }}</td>

                            <td>{{ $customer->national_code }}</td>

                            <td>{{ $customer->mobile }}</td>

                            <td>

                                {{ $customer->status->value }}

                            </td>

                            <td>

                                <a href="#"
                                   class="btn btn-sm btn-warning">

                                    <i class="bi bi-pencil"></i>

                                </a>

                                <button
                                    class="btn btn-sm btn-danger">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center py-4">

                                مشتری ثبت نشده است.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div class="d-flex justify-content-center mt-3">

            {{ $customers->links() }}

        </div>
        {{-- Pagination --}}

    </div>

@endsection

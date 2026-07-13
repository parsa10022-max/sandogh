@extends('layouts.app')

@section('title', 'مدیریت انواع وام')

@section('content')

    <div class="container-fluid">

        <x-page-header title="مدیریت انواع وام">

            <a
                href="{{ route('loan-types.create') }}"
                class="btn btn-primary">

                <i class="bi bi-plus-circle"></i>

                نوع وام جدید

            </a>

        </x-page-header>

        {{-- Search --}}
        <x-search-box
            :action="route('loan-types.index')"
        />

        {{-- Table --}}
        <x-datatable.table>

            <div class="card-body p-0">

                <table class="table table-hover table-striped align-middle mb-0">

                    <thead class="table-dark">

                    <tr>

                        <th>نام نوع وام</th>

                        <th>پیش‌شماره</th>

                        <th>وضعیت</th>

                        <th width="120">عملیات</th>

                    </tr>

                    </thead>

                    <tbody>

                    @forelse($loanTypes as $loanType)

                        <tr>

                            <td>{{ $loanType->name }}</td>

                            <td>{{ $loanType->prefix }}</td>

                            <td>
                                {{ $loanType->is_active->label() }}
                            </td>

                            <td class="text-center">

                                <div class="d-flex justify-content-center gap-1">

                                    <x-action-buttons
                                        :edit-route="route('loan-types.edit', $loanType)"
                                        :change-status-route="route('loan-types.change-status', $loanType)"
                                    />
                                    

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4"
                                class="text-center py-4">

                                نوع وامی ثبت نشده است.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </x-datatable.table>

        <x-pagination :items="$loanTypes"/>

    </div>

@endsection

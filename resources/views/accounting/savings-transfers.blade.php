@extends('layouts.app')

@section('title', 'واریزهای پس‌انداز')

@section('content')

    <div class="container-fluid accounting-page">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">

            <div>
                <h1 class="h4 fw-bold mb-1">
                    <i class="bi bi-wallet2 me-1"></i>
                    واریزهای پس‌انداز
                </h1>

                <div class="text-muted small">
                    واریزهای پرداخت‌شده در انتظار ثبت حسابداری
                </div>
            </div>

            <a href="{{ route('admin.accounting.index') }}"
               class="btn btn-light border rounded-3">

                <i class="bi bi-arrow-right me-1"></i>
                حسابداری

            </a>

        </div>


        <div class="card border-0 shadow-sm rounded-4">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                        <tr>

                            <th class="px-3">#</th>

                            <th>پرداخت‌کننده</th>

                            <th>صاحب حساب</th>

                            <th>مبلغ</th>

                            <th>تاریخ پرداخت</th>

                            <th class="text-end px-3">عملیات</th>

                        </tr>

                        </thead>


                        <tbody>

                        @forelse($transfers as $transfer)

                            <tr>

                                <td class="px-3">
                                    {{ $transfers->firstItem() + $loop->index }}
                                </td>


                                <td>

                                    <div class="fw-semibold">
                                        {{ $transfer->sender?->name ?? '---' }}
                                    </div>

                                </td>


                                <td>

                                    <div class="fw-semibold">
                                        {{ $transfer->receiver?->name ?? '---' }}
                                    </div>

                                </td>


                                <td>

                                    <span class="fw-bold">
                                        {{ $transfer->amount ?? 0 }}
                                    </span>

                                    <span class="text-muted small">
                                        تومان
                                    </span>

                                </td>


                                <td>

                                    @if($transfer->paid_at)

                                        {{ $transfer->paid_at->format('Y/m/d H:i') }}

                                    @else

                                        ---

                                    @endif

                                </td>


                                <td class="text-end px-3">

                                    <form method="POST"
                                          action="{{ route('admin.accounting.confirm', ['type' => 'savings-transfer', 'id' => $transfer->id]) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('آیا این عملیات به عنوان ثبت‌شده در حسابداری تأیید شود؟')">

                                        @csrf

                                        <button type="submit"
                                                class="btn btn-sm btn-success rounded-3">

                                            <i class="bi bi-check2-circle me-1"></i>
                                            تأیید ثبت حسابداری

                                        </button>

                                    </form>

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td colspan="6" class="text-center py-5">

                                    <div class="text-muted">

                                        <i class="bi bi-check-circle fs-2 d-block mb-2"></i>

                                        عملیات واریز در انتظاری وجود ندارد.

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        @if($transfers->hasPages())

            <div class="mt-4">
                {{ $transfers->links() }}
            </div>

        @endif

    </div>

@endsection

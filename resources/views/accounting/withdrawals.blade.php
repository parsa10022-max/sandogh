@extends('layouts.app')

@section('title', 'برداشت‌های پس‌انداز')

@section('content')

    <div class="container-fluid accounting-page">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">

            <div>
                <h1 class="h4 fw-bold mb-1">
                    <i class="bi bi-cash-stack me-1"></i>
                    برداشت‌های پس‌انداز
                </h1>

                <div class="text-muted small">
                    برداشت‌های پرداخت‌شده در انتظار ثبت حسابداری
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

                            <th>عضو</th>

                            <th>مبلغ</th>

                            <th>پرداخت‌کننده</th>

                            <th>تاریخ پرداخت</th>

                            <th class="text-end px-3">عملیات</th>

                        </tr>

                        </thead>


                        <tbody>

                        @forelse($withdrawals as $withdrawal)

                            <tr>

                                <td class="px-3">
                                    {{ $withdrawals->firstItem() + $loop->index }}
                                </td>


                                <td>

                                    <div class="fw-semibold">
                                        {{ $withdrawal->account?->customer?->name ?? '---' }}
                                    </div>

                                </td>


                                <td>

                                    <span class="fw-bold">
                                        {{ $withdrawal->amount ?? 0 }}
                                    </span>

                                    <span class="text-muted small">
                                        تومان
                                    </span>

                                </td>


                                <td>
                                    {{ $withdrawal->paidBy?->name ?? '---' }}
                                </td>


                                <td>

                                    @if($withdrawal->paid_at)

                                        {{ $withdrawal->paid_at->format('Y/m/d H:i') }}

                                    @else

                                        ---

                                    @endif

                                </td>


                                <td class="text-end px-3">

                                    <form method="POST"
                                          action="{{ route('admin.accounting.confirm', ['type' => 'withdrawal', 'id' => $withdrawal->id]) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('آیا این برداشت به عنوان ثبت‌شده در حسابداری تأیید شود؟')">

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

                                        برداشت در انتظاری وجود ندارد.

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        @if($withdrawals->hasPages())

            <div class="mt-4">
                {{ $withdrawals->links() }}
            </div>

        @endif

    </div>

@endsection

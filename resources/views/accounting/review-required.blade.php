@extends('layouts.app')

@section('title', 'پرداخت‌های نیازمند بررسی')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h4 mb-2">پرداخت‌های نیازمند بررسی</h1>
                <p class="text-muted mb-0">
                    پرداخت‌هایی که نیاز به تطبیق با سوابق بانکی دارند.
                </p>
            </div>

            <a href="{{ route('admin.accounting.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right"></i>
                بازگشت به حسابداری
            </a>
        </div>

        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            این پرداخت‌ها به‌صورت خودکار به قسط اعمال نشده‌اند.
            پیش از هر اقدام مالی، اطلاعات را با سوابق واقعی بانک تطبیق دهید.
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>شناسه</th>
                            <th>نوع پرداخت</th>
                            <th>شناسه مرجع</th>
                            <th>مبلغ (ریال)</th>
                            <th>کد پیگیری</th>
                            <th>شناسه تراکنش بانک</th>
                            <th>شماره مرجع بانک</th>
                            <th>تاریخ ثبت</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($intents as $intent)
                            <tr>
                                <td>{{ fa_number($intent->id) }}</td>
                                <td>{{ $intent->payment_type }}</td>
                                <td>{{ fa_number($intent->reference_id) }}</td>
                                <td>{{ fa_number(number_format($intent->amount)) }}</td>
                                <td>{{ $intent->tracking_code ?? '—' }}</td>
                                <td>{{ $intent->gateway_transaction_id ?? '—' }}</td>
                                <td>{{ $intent->gateway_reference_number ?? '—' }}</td>
                                <td>
                                    {{ $intent->updated_at?->format('Y/m/d H:i') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                                    پرداختی برای بررسی وجود ندارد.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($intents->hasPages())
                <div class="card-footer bg-white">
                    {{ $intents->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

<?php

namespace App\Services\Reports;

use App\Models\DonationPayment;
use App\Models\LoanPayment;
use App\Models\SavingsTransfer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GatewayTransactionsReportService
{
    /**
     * دریافت تراکنش‌های دارای درگاه پرداخت
     */
    public function getTransactions(
        ?string $from = null,
        ?string $to = null,
        ?string $type = null
    ): Collection {
        $transactions = collect();

        /*
        |--------------------------------------------------------------------------
        | واریز به حساب پس‌انداز
        |--------------------------------------------------------------------------
        */
        if (
            $type === null ||
            in_array($type, [
                'savings_own',
                'savings_other',
            ], true)
        ) {
            $savingsTransfers = SavingsTransfer::query()
                ->with([
                    'sender',
                    'receiver.user',
                ])
                ->whereNotNull('gateway')
                ->where('gateway', '!=', '')
                ->when($from, function ($query) use ($from) {
                    $query->where(function ($q) use ($from) {
                        $q->whereDate('paid_at', '>=', $from)
                            ->orWhere(function ($q2) use ($from) {
                                $q2->whereNull('paid_at')
                                    ->whereDate('created_at', '>=', $from);
                            });
                    });
                })
                ->when($to, function ($query) use ($to) {
                    $query->where(function ($q) use ($to) {
                        $q->whereDate('paid_at', '<=', $to)
                            ->orWhere(function ($q2) use ($to) {
                                $q2->whereNull('paid_at')
                                    ->whereDate('created_at', '<=', $to);
                            });
                    });
                })
                ->get();

            foreach ($savingsTransfers as $transfer) {

                $receiverUserId = $transfer->receiver?->user?->id;

                $isOwn =
                    $receiverUserId !== null &&
                    (int) $receiverUserId === (int) $transfer->sender_user_id;

                $transactionType = $isOwn
                    ? 'savings_own'
                    : 'savings_other';

                if ($type !== null && $type !== $transactionType) {
                    continue;
                }

                $transactions->push([
                    'id' => $transfer->id,

                    'type' => $transactionType,

                    'type_label' => $isOwn
                        ? 'واریز به حساب پس‌انداز خود'
                        : 'واریز به حساب پس‌انداز دیگران',

                    'amount' => (int) $transfer->amount,

                    'status' => $transfer->status,

                    'status_label' => match ($transfer->status) {
                        'paid' => 'موفق',
                        'failed' => 'ناموفق',
                        'pending' => 'در انتظار',
                        default => $transfer->status ?: '—',
                    },

                    'gateway' => $transfer->gateway?->value
                        ?? $transfer->gateway
                        ?? '—',

                    'tracking_code' => $transfer->tracking_code,

                    'bank_transaction_id' =>
                        $transfer->bank_transaction_id,

                    'bank_reference_number' =>
                        $transfer->bank_reference_number,

                    'date' => $transfer->paid_at
                        ?? $transfer->created_at,

                    'payer' =>
                        $transfer->sender?->name
                        ?? $transfer->sender?->mobile
                        ?? '—',

                    'receiver' =>
                        $transfer->receiver?->full_name
                        ?? $transfer->receiver?->name
                        ?? '—',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | پرداخت اقساط
        |--------------------------------------------------------------------------
        */
        if (
            $type === null ||
            in_array($type, [
                'installment_own',
                'installment_other',
            ], true)
        ) {
            $loanPayments = LoanPayment::query()
                ->with([
                    'user',
                    'loan.customer.user',
                    'installment',
                ])
                ->whereNotNull('gateway')
                ->where('gateway', '!=', '')
                ->when($from, function ($query) use ($from) {
                    $query->whereDate('paid_at', '>=', $from);
                })
                ->when($to, function ($query) use ($to) {
                    $query->whereDate('paid_at', '<=', $to);
                })
                ->get();

            foreach ($loanPayments as $payment) {

                $payerUserId = $payment->user_id;

                $ownerUserId =
                    $payment->loan?->customer?->user?->id;

                $isOwn =
                    $payerUserId !== null &&
                    $ownerUserId !== null &&
                    (int) $payerUserId === (int) $ownerUserId;

                $transactionType = $isOwn
                    ? 'installment_own'
                    : 'installment_other';

                if ($type !== null && $type !== $transactionType) {
                    continue;
                }

                $transactions->push([
                    'id' => $payment->id,

                    'type' => $transactionType,

                    'type_label' => $isOwn
                        ? 'پرداخت قسط خود'
                        : 'پرداخت قسط دیگران',

                    'amount' => (int) $payment->amount,

                    // LoanPayment فقط بعد از پرداخت موفق ایجاد می‌شود.
                    'status' => 'paid',

                    'status_label' => 'موفق',

                    'gateway' => $payment->gateway?->value
                        ?? $payment->gateway
                        ?? '—',

                    'tracking_code' =>
                        $payment->tracking_code,

                    'bank_transaction_id' =>
                        $payment->bank_transaction_id,

                    'bank_reference_number' =>
                        $payment->bank_reference_number,

                    'date' => $payment->paid_at,

                    'payer' =>
                        $payment->user?->name
                        ?? $payment->user?->mobile
                        ?? '—',

                    'receiver' =>
                        $payment->loan?->customer?->full_name
                        ?? $payment->loan?->customer?->name
                        ?? '—',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | کمک سیستمی
        |--------------------------------------------------------------------------
        */
        if (
            $type === null ||
            $type === 'system_help'
        ) {
            $donations = DonationPayment::query()
                ->with([
                    'customer',
                    'account',
                ])
                ->whereNotNull('gateway')
                ->where('gateway', '!=', '')
                ->when($from, function ($query) use ($from) {
                    $query->where(function ($q) use ($from) {
                        $q->whereDate('paid_at', '>=', $from)
                            ->orWhere(function ($q2) use ($from) {
                                $q2->whereNull('paid_at')
                                    ->whereDate('created_at', '>=', $from);
                            });
                    });
                })
                ->when($to, function ($query) use ($to) {
                    $query->where(function ($q) use ($to) {
                        $q->whereDate('paid_at', '<=', $to)
                            ->orWhere(function ($q2) use ($to) {
                                $q2->whereNull('paid_at')
                                    ->whereDate('created_at', '<=', $to);
                            });
                    });
                })
                ->get();

            foreach ($donations as $donation) {

                $status = match ((int) $donation->status) {
                    1 => 'paid',
                    2 => 'failed',
                    default => 'pending',
                };

                $statusLabel = match ($status) {
                    'paid' => 'موفق',
                    'failed' => 'ناموفق',
                    'pending' => 'در انتظار',
                    default => '—',
                };

                $transactions->push([
                    'id' => $donation->id,

                    'type' => 'system_help',

                    'type_label' => 'کمک سیستمی',

                    'amount' => (int) $donation->amount,

                    'status' => $status,

                    'status_label' => $statusLabel,

                    'gateway' => $donation->gateway?->value
                        ?? $donation->gateway
                        ?? '—',

                    'tracking_code' =>
                        $donation->tracking_code,

                    'bank_transaction_id' =>
                        $donation->bank_transaction_id,

                    'bank_reference_number' =>
                        $donation->bank_reference_number,

                    'date' => $donation->paid_at
                        ?? $donation->created_at,

                    'payer' =>
                        $donation->customer?->full_name
                        ?? $donation->customer?->name
                        ?? 'کمک‌کننده',

                    'receiver' =>
                        $donation->account?->account_number
                        ?? 'حساب صندوق',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | مرتب‌سازی نهایی
        |--------------------------------------------------------------------------
        */
        return $transactions
            ->sortByDesc(function ($transaction) {

                if (! $transaction['date']) {
                    return 0;
                }

                return Carbon::parse(
                    $transaction['date']
                )->timestamp;
            })
            ->values();
    }

    /**
     * خلاصه آماری گزارش
     */
    public function getSummary(Collection $transactions): array
    {
        return [
            'total_count' => $transactions->count(),

            'successful_count' => $transactions
                ->where('status', 'paid')
                ->count(),

            'failed_count' => $transactions
                ->where('status', 'failed')
                ->count(),

            'pending_count' => $transactions
                ->where('status', 'pending')
                ->count(),

            'successful_amount' => $transactions
                ->where('status', 'paid')
                ->sum('amount'),

            'failed_amount' => $transactions
                ->where('status', 'failed')
                ->sum('amount'),

            'pending_amount' => $transactions
                ->where('status', 'pending')
                ->sum('amount'),
        ];
    }
}

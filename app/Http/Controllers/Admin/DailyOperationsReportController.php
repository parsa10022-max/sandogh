<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountingStatus;
use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DonationPayment;
use App\Models\LoanPayment;
use App\Models\LoanRequest;
use App\Models\SavingsTransfer;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DailyOperationsReportController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $this->convertJalaliDate(
            $request->input('from_date')
        );

        $toDate = $this->convertJalaliDate(
            $request->input('to_date')
        );

        $operationType = $request->input('operation_type', 'all');
        $customerId = $request->input('customer_id');
        $status = $request->input('status', 'all');
        $onlyConfirmed = $request->boolean('only_confirmed');

        /*
        |--------------------------------------------------------------------------
        | 1. ثبت‌نام مشتری
        |--------------------------------------------------------------------------
        */

        $customers = Customer::query()
            ->with('user')
            ->when(
                $fromDate,
                fn (Builder $query) => $query->whereDate(
                    'created_at', '>=', $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) => $query->whereDate(
                    'created_at', '<=', $toDate
                )
            )
            ->when(
                $customerId,
                fn (Builder $query) => $query->where('id', $customerId)
            )
            ->latest('created_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 2. واریز به پس‌انداز خود و دیگران
        |--------------------------------------------------------------------------
        */

        $savingsTransfers = \App\Models\SavingsTransfer::query()
            ->with([
                'sender',
                'receiver.user',
                'receiver.accounts',
                'account.customer',
            ])
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->when(
                $fromDate,
                fn (Builder $query) => $query->whereDate(
                    'paid_at', '>=', $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) => $query->whereDate(
                    'paid_at', '<=', $toDate
                )
            )
            ->when(
                $customerId,
                function (Builder $query) use ($customerId) {
                    $query->whereIn(
                        'sender_user_id',
                        User::query()
                            ->where('customer_id', $customerId)
                            ->select('id')
                    );
                }
            )
            ->latest('paid_at')
            ->latest('id')
            ->get();

        if ($onlyConfirmed) {
            $savingsTransfers = $savingsTransfers->filter(
                fn ($transfer) =>
                    $transfer->accounting_status === AccountingStatus::CONFIRMED
            );
        }

        $ownSavingsTransfers = $savingsTransfers->filter(
            function ($transfer) {
                $receiverUserId = $transfer->receiver?->user?->id;

                return $receiverUserId !== null
                    && $receiverUserId === $transfer->sender_user_id;
            }
        );

        $otherSavingsTransfers = $savingsTransfers->filter(
            function ($transfer) {
                $receiverUserId = $transfer->receiver?->user?->id;

                return $receiverUserId === null
                    || $receiverUserId !== $transfer->sender_user_id;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | 3. پرداخت اقساط
        |--------------------------------------------------------------------------
        */

        $loanPayments = LoanPayment::query()
            ->with([
                'loan.customer.accounts',
                'loan.customer.user',
                'installment',
                'user.customer',
            ])
            ->whereNotNull('paid_at')
            ->when(
                $fromDate,
                fn (Builder $query) => $query->whereDate(
                    'paid_at', '>=', $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) => $query->whereDate(
                    'paid_at', '<=', $toDate
                )
            )
            ->when(
                $customerId,
                function (Builder $query) use ($customerId) {
                    $query->whereIn(
                        'user_id',
                        User::query()
                            ->where('customer_id', $customerId)
                            ->select('id')
                    );
                }
            )
            ->latest('paid_at')
            ->latest('id')
            ->get();

        if ($onlyConfirmed) {
            $loanPayments = $loanPayments->filter(
                fn ($payment) =>
                    $payment->accounting_status === AccountingStatus::CONFIRMED
            );
        }

        $ownLoanPayments = $loanPayments->filter(
            function ($payment) {
                $customerUserId = $payment->loan?->customer?->user?->id;

                return $customerUserId !== null
                    && $customerUserId === $payment->user_id;
            }
        );

        $otherLoanPayments = $loanPayments->filter(
            function ($payment) {
                $customerUserId = $payment->loan?->customer?->user?->id;

                return $customerUserId === null
                    || $customerUserId !== $payment->user_id;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | 4. کمک و صدقه
        |--------------------------------------------------------------------------
        */

        $donations = DonationPayment::query()
            ->with(['customer', 'account'])
            ->where('status', 1)
            ->when(
                $fromDate,
                fn (Builder $query) => $query->whereDate(
                    'created_at', '>=', $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) => $query->whereDate(
                    'created_at', '<=', $toDate
                )
            )
            ->when(
                $customerId,
                fn (Builder $query) => $query->where(
                    'customer_id', $customerId
                )
            )
            ->latest('created_at')
            ->latest('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 5. درخواست وام
        |--------------------------------------------------------------------------
        */

        $loanRequests = LoanRequest::query()
            ->with(['customer', 'loanType', 'reviewer'])
            ->when(
                $fromDate,
                fn (Builder $query) => $query->whereDate(
                    'created_at', '>=', $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) => $query->whereDate(
                    'created_at', '<=', $toDate
                )
            )
            ->when(
                $customerId,
                fn (Builder $query) => $query->where(
                    'customer_id', $customerId
                )
            )
            ->when(
                $status !== 'all',
                fn (Builder $query) => $query->where('status', $status)
            )
            ->latest('created_at')
            ->latest('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 6. برداشت‌های پرداخت‌شده
        |--------------------------------------------------------------------------
        |
        | فقط برداشت نهایی‌شده در گزارش عملیات انجام‌شده نمایش داده می‌شود.
        | تاریخ گزارش بر اساس paid_at است.
        |
        */

        $withdrawals = Withdrawal::query()
            ->with(['account.customer'])
            ->where('status', WithdrawalStatus::PAID->value)
            ->whereNotNull('paid_at')
            ->when(
                $fromDate,
                fn (Builder $query) => $query->whereDate(
                    'paid_at', '>=', $fromDate
                )
            )
            ->when(
                $toDate,
                fn (Builder $query) => $query->whereDate(
                    'paid_at', '<=', $toDate
                )
            )
            ->when(
                $customerId,
                function (Builder $query) use ($customerId) {
                    $query->whereHas(
                        'account',
                        fn (Builder $accountQuery) =>
                        $accountQuery->where(
                            'customer_id', $customerId
                        )
                    );
                }
            )
            ->latest('paid_at')
            ->latest('id')
            ->get();

        if ($onlyConfirmed) {
            $withdrawals = $withdrawals->filter(
                fn ($withdrawal) =>
                    $withdrawal->accounting_status === AccountingStatus::CONFIRMED
            );
        }

        /*
        |--------------------------------------------------------------------------
        | فیلتر نوع عملیات
        |--------------------------------------------------------------------------
        */

        switch ($operationType) {
            case 'registration':
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $otherLoanPayments = collect();
                $donations = collect();
                $loanRequests = collect();
                $withdrawals = collect();
                break;

            case 'saving_own':
                $customers = collect();
                $otherSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $otherLoanPayments = collect();
                $donations = collect();
                $loanRequests = collect();
                $withdrawals = collect();
                break;

            case 'saving_other':
                $customers = collect();
                $ownSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $otherLoanPayments = collect();
                $donations = collect();
                $loanRequests = collect();
                $withdrawals = collect();
                break;

            case 'loan_payment_own':
                $customers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $otherLoanPayments = collect();
                $donations = collect();
                $loanRequests = collect();
                $withdrawals = collect();
                break;

            case 'loan_payment_other':
                $customers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $donations = collect();
                $loanRequests = collect();
                $withdrawals = collect();
                break;

            case 'donation':
                $customers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $otherLoanPayments = collect();
                $loanRequests = collect();
                $withdrawals = collect();
                break;

            case 'loan_request':
                $customers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $otherLoanPayments = collect();
                $donations = collect();
                $withdrawals = collect();
                break;

            case 'withdrawal':
                $customers = collect();
                $ownSavingsTransfers = collect();
                $otherSavingsTransfers = collect();
                $ownLoanPayments = collect();
                $otherLoanPayments = collect();
                $donations = collect();
                $loanRequests = collect();
                break;

            case 'all':
            default:
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | مجموع مبالغ
        |--------------------------------------------------------------------------
        */

        $totals = [
            'own_savings_transfers' => $ownSavingsTransfers->sum('amount'),
            'other_savings_transfers' => $otherSavingsTransfers->sum('amount'),
            'own_loan_payments' => $ownLoanPayments->sum('amount'),
            'other_loan_payments' => $otherLoanPayments->sum('amount'),
            'donations' => $donations->sum('amount'),
            'withdrawals' => $withdrawals->sum('amount'),
        ];

        /*
        |--------------------------------------------------------------------------
        | مجموع دریافت‌ها
        |--------------------------------------------------------------------------
        |
        | برداشت‌ها از این شاخص کسر نمی‌شوند؛ این عدد مجموع دریافت‌هاست،
        | نه خالص گردش مالی صندوق.
        |
        */

        $totalReceived =
            $totals['own_savings_transfers']
            + $totals['other_savings_transfers']
            + $totals['own_loan_payments']
            + $totals['other_loan_payments']
            + $totals['donations'];

        /*
        |--------------------------------------------------------------------------
        | تعداد عملیات
        |--------------------------------------------------------------------------
        */

        $counts = [
            'customers' => $customers->count(),
            'own_savings_transfers' => $ownSavingsTransfers->count(),
            'other_savings_transfers' => $otherSavingsTransfers->count(),
            'own_loan_payments' => $ownLoanPayments->count(),
            'other_loan_payments' => $otherLoanPayments->count(),
            'donations' => $donations->count(),
            'loan_requests' => $loanRequests->count(),
            'withdrawals' => $withdrawals->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | اعضا برای فیلتر
        |--------------------------------------------------------------------------
        */

        $customersForFilter = Customer::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get([
                'id',
                'customer_code',
                'first_name',
                'last_name',
            ]);

        return view(
            'admin.reports.daily-operations',
            compact(
                'customers',
                'ownSavingsTransfers',
                'otherSavingsTransfers',
                'ownLoanPayments',
                'otherLoanPayments',
                'donations',
                'loanRequests',
                'withdrawals',
                'totals',
                'counts',
                'customersForFilter',
                'operationType',
                'customerId',
                'status',
                'onlyConfirmed',
                'fromDate',
                'toDate',
                'totalReceived',
            )
        );
    }

    private function convertJalaliDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return \Morilog\Jalali\Jalalian::fromFormat(
                'Y/m/d',
                trim($date)
            )->toCarbon()->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}

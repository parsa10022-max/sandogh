<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountingStatus;
use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\LoanPayment;
use App\Models\SavingsTransfer;
use App\Models\Withdrawal;
use App\Services\Accounting\AccountingConfirmationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function __construct(
        private readonly AccountingConfirmationService $service
    ) {
    }

    /**
     * داشبورد عملیات نیازمند ثبت حسابداری
     */
    public function index(): View
    {
        $status = AccountingStatus::PENDING;

        /*
        |--------------------------------------------------------------------------
        | واریز به حساب پس‌انداز
        |--------------------------------------------------------------------------
        */

        $pendingTransfers = SavingsTransfer::query()
            ->where('status', 'paid')
            ->where('accounting_status', $status);

        /*
        |--------------------------------------------------------------------------
        | واریز به حساب پس‌انداز خود
        |
        | فرستنده و صاحب حساب مقصد یک نفر هستند.
        |--------------------------------------------------------------------------
        */

        $savingsTransfersOwnCount = (clone $pendingTransfers)
            ->whereHas('receiver.user', function ($query) {
                $query->whereColumn(
                    'users.id',
                    'savings_transfers.sender_user_id'
                );
            })
            ->count();

        /*
        |--------------------------------------------------------------------------
        | واریز به حساب پس‌انداز دیگران
        |
        | فرستنده و صاحب حساب مقصد متفاوت هستند.
        |--------------------------------------------------------------------------
        */

        $savingsTransfersOtherCount = (clone $pendingTransfers)
            ->whereHas('receiver.user', function ($query) {
                $query->whereColumn(
                    'users.id',
                    'savings_transfers.sender_user_id'
                );
            }, '<', 1)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | برداشت از حساب پس‌انداز
        |--------------------------------------------------------------------------
        */

        $withdrawalsCount = Withdrawal::query()
            ->where(
                'status',
                WithdrawalStatus::PAID
            )
            ->where(
                'accounting_status',
                $status
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | پرداخت اقساط
        |--------------------------------------------------------------------------
        */

        $pendingLoanPayments = LoanPayment::query()
            ->whereNotNull('paid_at')
            ->where(
                'accounting_status',
                $status
            );

        /*
        |--------------------------------------------------------------------------
        | پرداخت قسط خود
        |
        | پرداخت‌کننده همان صاحب وام است.
        |--------------------------------------------------------------------------
        */

        $loanPaymentsOwnCount = (clone $pendingLoanPayments)
            ->whereHas('loan.customer.user', function ($query) {
                $query->whereColumn(
                    'users.id',
                    'loan_payments.user_id'
                );
            })
            ->count();

        /*
        |--------------------------------------------------------------------------
        | پرداخت قسط دیگران
        |--------------------------------------------------------------------------
        */

        $loanPaymentsOtherCount = (clone $pendingLoanPayments)
            ->whereHas('loan.customer.user', function ($query) {
                $query->whereColumn(
                    'users.id',
                    'loan_payments.user_id'
                );
            }, '<', 1)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | مجموع عملیات در انتظار ثبت حسابداری
        |--------------------------------------------------------------------------
        */

        $totalCount =
            $savingsTransfersOwnCount
            + $savingsTransfersOtherCount
            + $withdrawalsCount
            + $loanPaymentsOwnCount
            + $loanPaymentsOtherCount;

        return view(
            'accounting.index',
            compact(
                'savingsTransfersOwnCount',
                'savingsTransfersOtherCount',
                'withdrawalsCount',
                'loanPaymentsOwnCount',
                'loanPaymentsOtherCount',
                'totalCount'
            )
        );
    }

    /**
     * لیست واریزهای پس‌انداز
     */
    public function savingsTransfers(): View
    {
        $transfers = SavingsTransfer::query()
            ->with([
                'sender',
                'receiver',
                'receiver.user',
                'account',
            ])
            ->where('status', 'paid')
            ->where(
                'accounting_status',
                AccountingStatus::PENDING
            )
            ->latest('paid_at')
            ->paginate(20);

        return view(
            'accounting.savings-transfers',
            compact('transfers')
        );
    }

    /**
     * لیست برداشت‌ها
     */
    public function withdrawals(): View
    {
        $withdrawals = Withdrawal::query()
            ->with([
                'account.customer',
                'paidBy',
            ])
            ->where(
                'status',
                WithdrawalStatus::PAID
            )
            ->where(
                'accounting_status',
                AccountingStatus::PENDING
            )
            ->latest('paid_at')
            ->paginate(20);

        return view(
            'accounting.withdrawals',
            compact('withdrawals')
        );
    }

    /**
     * لیست پرداخت اقساط
     */
    public function loanPayments(): View
    {
        $payments = LoanPayment::query()
            ->with([
                'loan.customer',
                'loan.customer.user',
                'installment',
                'user',
            ])
            ->whereNotNull('paid_at')
            ->where(
                'accounting_status',
                AccountingStatus::PENDING
            )
            ->latest('paid_at')
            ->paginate(20);

        return view(
            'accounting.loan-payments',
            compact('payments')
        );
    }

    /**
     * تأیید یک عملیات
     */
    public function confirm(
        string $type,
        int $id
    ): RedirectResponse {

        $model = match ($type) {

            'savings-transfer' =>
            SavingsTransfer::findOrFail($id),

            'withdrawal' =>
            Withdrawal::findOrFail($id),

            'loan-payment' =>
            LoanPayment::findOrFail($id),

            default =>
            abort(404),
        };

        try {

            $this->service->confirm($model);

            return back()->with(
                'success',
                'عملیات با موفقیت به عنوان ثبت‌شده در حسابداری تأیید شد.'
            );

        } catch (\Throwable $e) {

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }
}

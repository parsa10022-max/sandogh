<?php

namespace App\Services\Accounting;

use App\Enums\AccountingStatus;
use App\Enums\WithdrawalStatus;
use App\Models\LoanPayment;
use App\Models\SavingsTransfer;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccountingConfirmationService
{
    /**
     * تأیید ثبت عملیات در نرم‌افزار حسابداری
     */
    public function confirm(Model $operation): Model
    {
        return DB::transaction(function () use ($operation) {

            $operation->refresh();

            /*
            |--------------------------------------------------------------------------
            | قبلاً تأیید شده؟
            |--------------------------------------------------------------------------
            */

            if (
                $operation->accounting_status
                === AccountingStatus::CONFIRMED
            ) {
                throw new \DomainException(
                    'این عملیات قبلاً در حسابداری تأیید شده است.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | عملیات باید واقعاً پرداخت شده باشد
            |--------------------------------------------------------------------------
            */

            if (! $this->isPaid($operation)) {
                throw new \DomainException(
                    'این عملیات هنوز پرداخت نشده است.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | ثبت تأیید حسابداری
            |--------------------------------------------------------------------------
            */

            $operation->update([
                'accounting_status' =>
                    AccountingStatus::CONFIRMED,

                'accounting_confirmed_by' =>
                    auth()->id(),

                'accounting_confirmed_at' =>
                    now(),
            ]);

            return $operation->fresh();
        });
    }

    /**
     * بررسی اینکه عملیات واقعاً پرداخت شده است.
     */
    private function isPaid(Model $operation): bool
    {
        return match (true) {

            $operation instanceof Withdrawal =>
            $operation->isPaid(),

            $operation instanceof SavingsTransfer =>
                $operation->status === 'paid',

            $operation instanceof LoanPayment =>
                $operation->paid_at !== null,

            default =>
            false,
        };
    }
}

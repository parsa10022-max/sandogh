<?php

namespace App\Services\Account;

use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Enums\PaymentMethod;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use App\Models\Withdrawal;
use App\Enums\WithdrawalStatus;
use App\Services\Account\AccountTransactionService;
use App\Services\Account\AccountTransactionNoService;
use Illuminate\Validation\ValidationException;
use App\Models\AccountTransaction;


class AccountService
{
    public function __construct(

        private readonly AccountTransactionService $accountTransactionService,

        private readonly AccountTransactionNoService $transactionNoService,

    ) {
    }

    public function deposit(
        Account $account,
        int $amount,
        PaymentMethod $paymentMethod,
        TransactionSource $source = TransactionSource::OPERATOR,
        ?string $description = null
    ): AccountTransaction {

        if ($amount < 50000) {
            throw new \InvalidArgumentException(
                'حداقل مبلغ واریز ۵۰,۰۰۰ ریال است.'
            );
        }

        return DB::transaction(function () use (
            $account,
            $amount,
            $paymentMethod,
            $source,
            $description
        ) {

            $balanceBefore = $account->balance;

            $balanceAfter = $balanceBefore + $amount;

            $account->increment(
                'balance',
                $amount
            );

            return $this->accountTransactionService->create(
                account: $account,
                type: TransactionType::DEPOSIT,
                source: $source,
                paymentMethod: $paymentMethod,
                amount: $amount,
                balanceBefore: $balanceBefore,
                balanceAfter: $balanceAfter,
                description: $description,
            );
        });
    }



    public function withdraw(
        Account $account,
        int $amount,
        PaymentMethod $paymentMethod,
        ?string $iban = null,
        ?string $description = null,
        ?int $createdBy = null,
    ): Withdrawal {

        if ($amount < 500000) {

            throw new \InvalidArgumentException(
                'حداقل مبلغ برداشت ۵۰۰,۰۰۰ ریال است.'
            );

        }

        if ($amount > $account->balance) {

            throw new \InvalidArgumentException(
                'موجودی حساب برای برداشت کافی نیست.'
            );

        }


        return DB::transaction(function () use (

            $account,
            $amount,
            $paymentMethod,
            $iban,
            $description,
            $createdBy

        ) {


            $balanceBefore = $account->balance;

            $balanceAfter = $balanceBefore - $amount;

            $account->decrement(
                'balance',
                $amount
            );


            $transaction = $this->accountTransactionService->create(

                account: $account,

                type: TransactionType::WITHDRAWAL,

                source: TransactionSource::ONLINE,

                paymentMethod: $paymentMethod,

                amount: $amount,

                balanceBefore: $balanceBefore,

                balanceAfter: $balanceAfter,

                description: $description,

                createdBy: $createdBy,

            );



            return Withdrawal::create([

                'account_id' => $account->id,

                'account_transaction_id' => $transaction->id,

                'amount' => $amount,

                'iban' => $iban ?? $account->customer->iban,

                'status' => WithdrawalStatus::PENDING,

                'description' => $description,

            ]);


        });

    }



    public function cancel(
        Withdrawal $withdrawal,
        int $customerId,
    ): Withdrawal {

        if ($withdrawal->account->customer_id !== $customerId) {

            throw ValidationException::withMessages([
                'withdrawal' => 'شما مجاز به لغو این درخواست نیستید.',
            ]);

        }


        if ($withdrawal->status !== WithdrawalStatus::PENDING) {

            throw ValidationException::withMessages([
                'withdrawal' => 'این درخواست دیگر قابل لغو نیست.',
            ]);

        }


        return DB::transaction(function () use ($withdrawal) {

            $account = $withdrawal->account;


            $balanceBefore = $account->balance;


            $balanceAfter = $balanceBefore + $withdrawal->amount;


            $account->increment(
                'balance',
                $withdrawal->amount
            );

            $this->accountTransactionService->create(

                account: $account,

                type: TransactionType::DEPOSIT,

                source: TransactionSource::ONLINE,

                paymentMethod: PaymentMethod::BANK_TRANSFER,

                amount: $withdrawal->amount,

                balanceBefore: $balanceBefore,

                balanceAfter: $balanceAfter,

                description: 'برگشت مبلغ برداشت لغو شده',

            );


            $withdrawal->update([

                'status' => WithdrawalStatus::CANCELLED,

            ]);


            return $withdrawal->fresh();

        });
    }

    public function rejectWithdrawal(
        Withdrawal $withdrawal
    ): Withdrawal {

        if ($withdrawal->status !== WithdrawalStatus::PENDING) {

            throw ValidationException::withMessages([
                'withdrawal' => 'این درخواست قابل رد نیست.',
            ]);

        }

        return DB::transaction(function () use ($withdrawal) {

            $account = $withdrawal->account;

            $balanceBefore = $account->balance;

            $balanceAfter = $balanceBefore + $withdrawal->amount;

            // برگشت مبلغ به حساب
            $account->update([
                'balance' => $balanceAfter,
            ]);

            // ثبت تراکنش برگشت وجه
            $this->accountTransactionService->create(

                account: $account,

                type: TransactionType::DEPOSIT,

                source: TransactionSource::OPERATOR,

                paymentMethod: PaymentMethod::BANK_TRANSFER,

                amount: $withdrawal->amount,

                balanceBefore: $balanceBefore,

                balanceAfter: $balanceAfter,

                description: 'برگشت مبلغ برداشت رد شده',

            );

            // تغییر وضعیت درخواست برداشت
            $withdrawal->update([
                'status' => WithdrawalStatus::REJECTED,
            ]);

            return $withdrawal->fresh();

        });

    }
    public function depositBalance(
        Account $account,
        int $amount
    ): void {

        $account->increment(
            'balance',
            $amount
        );

    }

    public function adjustBalance(
        Account $account,
        int $newBalance,
        ?string $description = null,
        ?int $createdBy = null,
    ): AccountTransaction {

        if ($newBalance < 0) {
            throw new \InvalidArgumentException(
                'موجودی نمی‌تواند منفی باشد.'
            );
        }

        return DB::transaction(function () use (
            $account,
            $newBalance,
            $description,
            $createdBy
        ) {

            $balanceBefore = $account->balance;

            if ($newBalance === $balanceBefore) {
                throw new \InvalidArgumentException(
                    'موجودی جدید با موجودی فعلی یکسان است.'
                );
            }

            $balanceAfter = $newBalance;

            $account->update([
                'balance' => $balanceAfter,
            ]);

            return $this->accountTransactionService->create(
                account: $account,
                type: TransactionType::ADJUSTMENT,
                source: TransactionSource::OPERATOR,
                paymentMethod: PaymentMethod::BANK_TRANSFER,
                amount: abs($newBalance - $balanceBefore),
                balanceBefore: $balanceBefore,
                balanceAfter: $balanceAfter,
                createdBy: $createdBy ?? auth()->id(),
                description: $description ?? 'اصلاح موجودی حساب',
            );
        });
    }
}

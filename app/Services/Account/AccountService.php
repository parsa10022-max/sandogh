<?php

namespace App\Services\Account;

use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Enums\WithdrawalStatus;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            $account = Account::query()
                ->lockForUpdate()
                ->findOrFail($account->id);

            $balanceBefore = $account->balance;
            $balanceAfter = $balanceBefore + $amount;

            $account->increment('balance', $amount);

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

        return DB::transaction(function () use (
            $account,
            $amount,
            $paymentMethod,
            $iban,
            $description,
            $createdBy
        ) {
            $account = Account::query()
                ->with('customer')
                ->lockForUpdate()
                ->findOrFail($account->id);

            if ($amount > $account->balance) {
                throw new \InvalidArgumentException(
                    'موجودی حساب برای برداشت کافی نیست.'
                );
            }

            $withdrawalIban = $iban ?? $account->customer?->iban;

            if (!$withdrawalIban) {
                throw new \InvalidArgumentException(
                    'شماره شبا برای برداشت مشخص نشده است.'
                );
            }

            $balanceBefore = $account->balance;
            $balanceAfter = $balanceBefore - $amount;

            $account->decrement('balance', $amount);

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
                'iban' => $withdrawalIban,
                'status' => WithdrawalStatus::PENDING,
                'description' => $description,
            ]);
        });
    }

    public function cancel(
        Withdrawal $withdrawal,
        int $customerId,
    ): Withdrawal {
        return DB::transaction(function () use (
            $withdrawal,
            $customerId
        ) {
            $withdrawal = Withdrawal::query()
                ->with('account')
                ->lockForUpdate()
                ->findOrFail($withdrawal->id);

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

            $account = Account::query()
                ->lockForUpdate()
                ->findOrFail($withdrawal->account_id);

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
        return DB::transaction(function () use ($withdrawal) {
            $withdrawal = Withdrawal::query()
                ->lockForUpdate()
                ->findOrFail($withdrawal->id);

            if ($withdrawal->status !== WithdrawalStatus::PENDING) {
                throw ValidationException::withMessages([
                    'withdrawal' => 'این درخواست قابل رد نیست.',
                ]);
            }

            $account = Account::query()
                ->lockForUpdate()
                ->findOrFail($withdrawal->account_id);

            $balanceBefore = $account->balance;
            $balanceAfter = $balanceBefore + $withdrawal->amount;

            $account->increment(
                'balance',
                $withdrawal->amount
            );

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
        if ($amount <= 0) {
            throw new \InvalidArgumentException(
                'مبلغ واریز باید بیشتر از صفر باشد.'
            );
        }

        DB::transaction(function () use ($account, $amount) {
            $account = Account::query()
                ->lockForUpdate()
                ->findOrFail($account->id);

            $account->increment(
                'balance',
                $amount
            );
        });
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
            $account = Account::query()
                ->lockForUpdate()
                ->findOrFail($account->id);

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

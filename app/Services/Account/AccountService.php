<?php

namespace App\Services\Account;

use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Enums\PaymentMethod;
use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Withdrawal;
use App\Enums\WithdrawalStatus;

use Illuminate\Validation\ValidationException;

class AccountService
{
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

            $account->update([
                'balance' => $balanceAfter,
            ]);

            return $this->createTransaction(
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


    private function createTransaction(
        Account $account,
        TransactionType $type,
        TransactionSource $source,
        PaymentMethod $paymentMethod,
        int $amount,
        int $balanceBefore,
        int $balanceAfter,
        ?string $description = null,
        ?int $createdBy = null,
    ): AccountTransaction {

        return AccountTransaction::create([
            'account_id'         => $account->id,
            'transaction_no'     => $this->generateTransactionNo(),
            'transaction_type'   => $type,
            'transaction_source' => $source,
            'payment_method'     => $paymentMethod,
            'amount'             => $amount,
            'balance_before'     => $balanceBefore,
            'balance_after'      => $balanceAfter,
            'transaction_date'   => now(),
            'created_by'         => $createdBy,
            'description'        => $description,
        ]);
    }


    private function generateTransactionNo(): string
    {
        return 'TRX-' . strtoupper(Str::random(10));
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


            $account->update([

                'balance' => $balanceAfter,

            ]);



            $transaction = $this->createTransaction(

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


            $account->update([
                'balance' => $balanceAfter,
            ]);


            $this->createTransaction(

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

            $account->update([
                'balance' => $balanceAfter,
            ]);

            $this->createTransaction(

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
}

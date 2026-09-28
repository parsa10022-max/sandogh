<?php

namespace App\Services\Account;

use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Database\QueryException;

class AccountTransactionService
{
    private const MAX_RETRIES = 5;

    public function __construct(
        private readonly AccountTransactionNoService $transactionNoService,
    ) {
    }

    public function create(
        Account $account,
        TransactionType $type,
        TransactionSource $source,
        PaymentMethod $paymentMethod,
        int $amount,
        int $balanceBefore,
        int $balanceAfter,
        ?int $createdBy = null,
        ?string $description = null,
    ): AccountTransaction {
        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {

            try {
                return AccountTransaction::create([

                    'account_id' => $account->id,

                    'transaction_no' =>
                        $this->transactionNoService->generate(),

                    'transaction_type' => $type,

                    'transaction_source' => $source,

                    'amount' => $amount,

                    'balance_before' => $balanceBefore,

                    'balance_after' => $balanceAfter,

                    'payment_method' => $paymentMethod,

                    'transaction_date' => today(),

                    'created_by' => $createdBy,

                    'description' => $description,

                ]);

            } catch (QueryException $e) {

                $errorInfo = $e->errorInfo ?? [];

                $isDuplicateKey =
                    ($errorInfo[1] ?? null) === 1062
                    && str_contains(
                        $e->getMessage(),
                        'transaction_no'
                    );

                if (
                    ! $isDuplicateKey
                    || $attempt === self::MAX_RETRIES
                ) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException(
            'Unable to create account transaction.'
        );
    }
}

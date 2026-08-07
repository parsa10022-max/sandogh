<?php

namespace App\Services\Account;

use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\AccountTransaction;

class AccountTransactionService
{
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

        return AccountTransaction::create([

            'account_id'          => $account->id,

            'transaction_no'      => $this->transactionNoService->generate(),

            'transaction_type'    => $type,

            'transaction_source'  => $source,

            'amount'              => $amount,

            'balance_before'      => $balanceBefore,

            'balance_after'       => $balanceAfter,

            'payment_method'      => $paymentMethod,

            'transaction_date'    => today(),

            'created_by'          => $createdBy,

            'description'         => $description,

        ]);
    }
}

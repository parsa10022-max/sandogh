<?php

namespace App\Services\Account;

use App\Models\AccountTransaction;
use Morilog\Jalali\Jalalian;

class AccountTransactionNoService
{
    private const PREFIX = 'AT';

    private const SEQUENCE_LENGTH = 6;

    public function generate(): string
    {
        $today = Jalalian::now()->format('Ymd');

        $last = AccountTransaction::query()
            ->where(
                'transaction_no',
                'like',
                self::PREFIX . $today . '%'
            )
            ->latest('id')
            ->first();

        $sequence = 1;

        if ($last) {

            $sequence =
                (int) substr(
                    $last->transaction_no,
                    -self::SEQUENCE_LENGTH
                ) + 1;

        }

        return sprintf(
            '%s%s%0' . self::SEQUENCE_LENGTH . 'd',
            self::PREFIX,
            $today,
            $sequence
        );
    }
}

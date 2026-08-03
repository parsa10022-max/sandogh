<?php

namespace App\Services\Payment;

use App\Models\SavingsTransfer;
use App\Services\Savings\SavingsTransferService;

class PaymentResolverService
{
    public function __construct(
        private readonly PaymentService $loanPaymentService,
        private readonly SavingsTransferService $savingsTransferService,
    ) {
    }

    /**
     * تایید پرداخت
     */
    public function verify(array $callbackData): mixed
    {
        /*
        |--------------------------------------------------------------------------
        | واریز به حساب پس‌انداز
        |--------------------------------------------------------------------------
        */

        if (
            isset($callbackData['reference_id']) &&
            SavingsTransfer::where(
                'id',
                $callbackData['reference_id']
            )->exists()
        ) {

            return $this->savingsTransferService
                ->verifyPayment($callbackData);

        }

        /*
        |--------------------------------------------------------------------------
        | پرداخت اقساط
        |--------------------------------------------------------------------------
        */

        return $this->loanPaymentService
            ->verifyPayment($callbackData);
    }
}

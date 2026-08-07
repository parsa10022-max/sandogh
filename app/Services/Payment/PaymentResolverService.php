<?php

namespace App\Services\Payment;


use App\Services\Savings\SavingsTransferService;
use App\Services\Donation\DonationPaymentService;

class PaymentResolverService
{
    public function __construct(

        private readonly PaymentService $loanPaymentService,

        private readonly SavingsTransferService $savingsTransferService,

        private readonly DonationPaymentService $donationPaymentService,

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
            ($callbackData['payment_type'] ?? null)
            ===
            'savings_transfer'
        ) {

            return $this->savingsTransferService
                ->verifyPayment($callbackData);

        }


        /*
        |--------------------------------------------------------------------------
        | پرداخت قسط وام
        |--------------------------------------------------------------------------
        */

        if (
            ($callbackData['payment_type'] ?? null)
            ===
            'installment'
        ) {

            return $this->loanPaymentService
                ->verifyPayment($callbackData);

        }
        /*
|--------------------------------------------------------------------------
| کمک مالی
|--------------------------------------------------------------------------
*/

        if (
            in_array(
                ($callbackData['payment_type'] ?? null),
                [
                    'donation_public',
                    'donation_customer'
                ]
            )
        ) {

            return $this->donationPaymentService
                ->verifyPayment($callbackData);

        }

        throw new \RuntimeException(
            'نوع پرداخت مشخص نیست.'
        );
    }
}

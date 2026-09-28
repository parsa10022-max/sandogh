<?php

namespace App\Services\Payment;

use App\Services\Donation\DonationPaymentService;
use App\Services\Savings\SavingsTransferService;

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
        $paymentType = $callbackData['payment_type'] ?? null;

        switch ($paymentType) {

            /*
            |--------------------------------------------------------------------------
            | واریز به حساب پس‌انداز
            |--------------------------------------------------------------------------
            */

            case 'savings_transfer':

                return $this->savingsTransferService
                    ->verifyPayment($callbackData);

            /*
            |--------------------------------------------------------------------------
            | پرداخت قسط وام
            |--------------------------------------------------------------------------
            */

            case 'installment':
            case 'installment_customer':
            case 'installment_other':

                return $this->loanPaymentService
                    ->verifyPayment($callbackData);

            /*
            |--------------------------------------------------------------------------
            | کمک مالی
            |--------------------------------------------------------------------------
            */

            case 'donation_public':
            case 'donation_customer':

                return $this->donationPaymentService
                    ->verifyPayment($callbackData);

            default:

                throw new \RuntimeException(
                    'نوع پرداخت مشخص نیست.'
                );
        }
    }
}

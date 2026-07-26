<?php

namespace App\Services\Payment;

use App\Models\LoanPayment;
use Morilog\Jalali\Jalalian;

class TrackingCodeService
{
    /**
     * پیشوند کد رهگیری پرداخت وام
     */
    private const PREFIX = 'LP';

    /**
     * طول شماره ترتیبی
     */
    private const SEQUENCE_LENGTH = 6;

    /**
     * تولید کد رهگیری
     */
    public function generate(): string
    {
        return $this->generateLoanPaymentCode();
    }

    /**
     * تولید کد رهگیری پرداخت وام
     */
    public function generateLoanPaymentCode(): string
    {
        $today = Jalalian::now()->format('Ymd');

        $lastPayment = LoanPayment::query()
            ->where('tracking_code', 'like', self::PREFIX . $today . '%')
            ->latest('id')
            ->first();

        $sequence = 1;

        if ($lastPayment) {

            $lastSequence = (int) substr(
                $lastPayment->tracking_code,
                -self::SEQUENCE_LENGTH
            );

            $sequence = $lastSequence + 1;
        }

        return sprintf(
            '%s%s%0' . self::SEQUENCE_LENGTH . 'd',
            self::PREFIX,
            $today,
            $sequence
        );
    }
}

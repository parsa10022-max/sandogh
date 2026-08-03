<?php

namespace App\Services\Savings;

use App\Models\SavingsTransfer;
use Morilog\Jalali\Jalalian;

class SavingsTransferTrackingCodeService
{
    private const PREFIX = 'ST';

    private const SEQUENCE_LENGTH = 6;


    public function generate(): string
    {
        $today = Jalalian::now()->format('Ymd');


        $lastTransfer = SavingsTransfer::query()
            ->where(
                'tracking_code',
                'like',
                self::PREFIX . $today . '%'
            )
            ->latest('id')
            ->first();


        $sequence = 1;


        if ($lastTransfer) {

            $lastSequence = (int) substr(
                $lastTransfer->tracking_code,
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

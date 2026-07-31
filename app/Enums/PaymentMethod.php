<?php

namespace App\Enums;

enum PaymentMethod: int
{
    case CASH = 1;                // نقدی
    case POS = 2;                 // دستگاه پوز
    case GATEWAY = 3;             // درگاه آنلاین
    case LOAN_DISBURSEMENT = 4;   // واریز مبلغ وام
    case BANK_TRANSFER = 5;       // انتقال بانکی

    public function label(): string
    {
        return match ($this) {
            self::CASH              => 'نقدی',
            self::POS               => 'دستگاه پوز',
            self::GATEWAY           => 'درگاه آنلاین',
            self::LOAN_DISBURSEMENT => 'واریز مبلغ وام',
            self::BANK_TRANSFER     => 'انتقال بانکی',
        };
    }
}

<?php

namespace App\Enums;

enum TransactionType: int
{
    case DEPOSIT = 1;              // واریز
    case WITHDRAWAL = 2;           // برداشت
    case TRANSFER = 3;             // انتقال
    case INSTALLMENT_PAYMENT = 4;  // پرداخت قسط
    case ADJUSTMENT = 5;            // اصلاح


    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'واریز',
            self::WITHDRAWAL => 'برداشت',
            self::TRANSFER => 'انتقال',
            self::INSTALLMENT_PAYMENT => 'پرداخت قسط',
            self::ADJUSTMENT => 'اصلاح',
        };
    }
}

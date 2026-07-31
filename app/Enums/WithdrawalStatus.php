<?php

namespace App\Enums;

enum WithdrawalStatus:int
{
    case REJECTED = -1;

    case CANCELLED = 0;

    case PENDING = 1;

    case PAID = 2;

    public function label(): string
    {
        return match ($this) {

            self::REJECTED => 'رد شده',

            self::CANCELLED => 'لغو شده',

            self::PENDING => 'در انتظار پرداخت',

            self::PAID => 'پرداخت شده',

        };
    }

    public function badge(): string
    {
        return match ($this) {

            self::REJECTED => 'danger',

            self::CANCELLED => 'secondary',

            self::PENDING => 'warning',

            self::PAID => 'success',

        };
    }
}

<?php

namespace App\Enums;

enum AccountingStatus: int
{
    case PENDING = 0;
    case CONFIRMED = 1;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار ثبت حسابداری',
            self::CONFIRMED => 'ثبت شده در حسابداری',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'success',
        };
    }
}

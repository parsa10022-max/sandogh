<?php

namespace App\Enums;

enum TransactionSource: int
{
    case ONLINE = 1;
    case OPERATOR = 2;
    case SYSTEM = 3;


    public function label(): string
    {
        return match ($this) {
            self::ONLINE => 'آنلاین',
            self::OPERATOR => 'اپراتور',
            self::SYSTEM => 'سیستم',
        };
    }
}

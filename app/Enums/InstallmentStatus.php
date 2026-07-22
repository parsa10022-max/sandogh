<?php

namespace App\Enums;

enum InstallmentStatus: int
{
    case UNPAID = 0;

    case PAID = 1;


    public function label(): string
    {
        return match ($this) {

            self::UNPAID => 'پرداخت نشده',

            self::PAID => 'پرداخت شده',

        };
    }


    public static function options(): array
    {
        return [

            self::UNPAID->value => self::UNPAID->label(),

            self::PAID->value => self::PAID->label(),

        ];
    }
}

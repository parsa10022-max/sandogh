<?php

namespace App\Enums;

enum DonationStatus:int
{
    case FAILED = 0;

    case SUCCESS = 1;

    case PENDING = 2;

    public function label(): string
    {
        return match ($this) {

            self::FAILED => 'ناموفق',

            self::SUCCESS => 'موفق',

            self::PENDING => 'در انتظار پرداخت',

        };
    }
}

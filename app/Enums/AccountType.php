<?php

namespace App\Enums;

enum AccountType: int
{
    case SAVING = 1;
    case CURRENT = 2;

    public function label(): string
    {
        return match ($this) {
            self::SAVING => 'پس‌انداز',
            self::CURRENT => 'جاری',
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::SAVING => '6111',
            self::CURRENT => '6112',
        };
    }
}

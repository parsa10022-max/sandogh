<?php

namespace App\Enums;

enum AccountStatus: int
{
    case CLOSED = 0;
    case ACTIVE = 1;
    case BLOCKED = 2;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::BLOCKED => 'مسدود',
            self::CLOSED => 'بسته',
        };
    }
}

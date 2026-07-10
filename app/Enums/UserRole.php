<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';

    case CEO = 'ceo';

    case BOARD_MEMBER = 'board_member';

    case OPERATOR = 'operator';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN        => 'مدیر سیستم',
            self::CEO          => 'مدیرعامل',
            self::BOARD_MEMBER => 'عضو هیئت مدیره',
            self::OPERATOR     => 'اپراتور',
        };
    }
}

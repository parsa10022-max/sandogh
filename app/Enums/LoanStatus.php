<?php

namespace App\Enums;

enum LoanStatus: int
{
    case ACTIVE = 1;
    case FINISHED = 2;
    case CANCELLED = 0;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::FINISHED => 'تسویه شده',
            self::CANCELLED => 'لغو شده',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($status) => [
                $status->value => $status->label(),
            ])
            ->toArray();
    }
}

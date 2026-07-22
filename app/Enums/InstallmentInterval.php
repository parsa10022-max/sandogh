<?php

namespace App\Enums;

enum InstallmentInterval: int
{
    case MONTHLY = 1;

    case THREE_MONTHS = 3;

    case SIX_MONTHS = 6;

    public function label(): string
    {
        return match ($this) {
            self::MONTHLY => 'ماهانه',
            self::THREE_MONTHS => 'سه ماهه',
            self::SIX_MONTHS => 'شش ماهه',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($interval) => [
                $interval->value => $interval->label(),
            ])
            ->toArray();
    }
}

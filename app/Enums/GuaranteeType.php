<?php

namespace App\Enums;

enum GuaranteeType: string
{
    // چک صیادی
    case CHECK = 'check';

    // سفته
    case PROMISSORY_NOTE = 'promissory_note';

    // سایر
    case OTHER = 'other';

    /**
     * عنوان فارسی
     */
    public function label(): string
    {
        return match ($this) {
            self::CHECK            => 'چک صیادی',
            self::PROMISSORY_NOTE  => 'سفته',
            self::OTHER            => 'سایر',
        };
    }

    /**
     * لیست برای Select
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $item) => [
                $item->value => $item->label(),
            ])
            ->toArray();
    }
}

<?php

namespace App\Enums;

enum GuarantorType: string
{
    /**
     * عضو صندوق
     */
    case CUSTOMER = 'customer';

    /**
     * خود وام‌گیرنده
     */
    case BORROWER = 'borrower';

    /**
     * شخص خارج از صندوق
     */
    case EXTERNAL = 'external';

    /**
     * عنوان فارسی
     */
    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER => 'عضو صندوق',
            self::BORROWER => 'خود وام‌گیرنده',
            self::EXTERNAL => 'شخص خارج از صندوق',
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

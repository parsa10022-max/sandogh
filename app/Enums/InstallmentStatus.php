<?php

namespace App\Enums;

enum InstallmentStatus: int
{
    case PENDING = 0;

    case PAID = 1;

    case OVERDUE = 2;

    case CANCELLED = 3;

    public function label(): string
    {
        return match ($this) {

            self::PENDING => 'در انتظار پرداخت',

            self::PAID => 'پرداخت شده',

            self::OVERDUE => 'معوق',

            self::CANCELLED => 'لغو شده',

        };
    }

    /**
     * آیا قسط پرداخت شده است؟
     */
    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    /**
     * آیا قسط در انتظار پرداخت است؟
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * آیا قسط معوق است؟
     */
    public function isOverdue(): bool
    {
        return $this === self::OVERDUE;
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [
                $status->value => $status->label(),
            ])
            ->toArray();
    }
}

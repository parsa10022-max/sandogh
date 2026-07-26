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

    /**
     * آیا وام فعال است؟
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * آیا وام تسویه شده است؟
     */
    public function isFinished(): bool
    {
        return $this === self::FINISHED;
    }

    /**
     * آیا وام لغو شده است؟
     */
    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
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

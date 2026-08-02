<?php

namespace App\Enums;

enum LoanRequestStatus: string
{
    case PENDING = 'pending';

    case APPROVED = 'approved';

    case REJECTED = 'rejected';

    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'در انتظار بررسی',
            self::APPROVED  => 'تایید شده',
            self::REJECTED  => 'رد شده',
            self::CANCELLED => 'لغو شده',
        };
    }
}

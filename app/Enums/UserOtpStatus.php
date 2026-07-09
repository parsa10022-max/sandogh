<?php

namespace App\Enums;

enum UserOtpStatus: string
{
    case PENDING = 'pending';

    case VERIFIED = 'verified';

    case EXPIRED = 'expired';

    case CANCELLED = 'cancelled';
}

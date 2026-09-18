<?php

namespace App\Enums;

enum CustomerActivationOtpStatus: string
{
    case PENDING = 'pending';

    case VERIFIED = 'verified';

    case CANCELLED = 'cancelled';
}

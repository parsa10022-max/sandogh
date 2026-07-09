<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';

    case BOARD_MEMBER = 'board_member';

    case OPERATOR = 'operator';

    case CUSTOMER = 'customer';
}

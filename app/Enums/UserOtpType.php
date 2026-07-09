<?php

namespace App\Enums;

enum UserOtpType: string
{
    case LOGIN = 'login';

    case PASSWORD_RESET = 'password_reset';

    case CHANGE_MOBILE = 'change_mobile';
}

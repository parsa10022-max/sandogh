<?php

namespace App\Enums;

enum FakeGatewayResult: string
{
    /**
     * پرداخت موفق
     */
    case SUCCESS = 'success';

    /**
     * پرداخت ناموفق
     */
    case FAILED = 'failed';

    /**
     * انصراف کاربر
     */
    case CANCELED = 'canceled';

    /**
     * پایان زمان پرداخت
     */
    case TIMEOUT = 'timeout';

    /**
     * عدم تایید بانک
     */
    case VERIFY_FAILED = 'verify_failed';

    /**
     * Callback تکراری
     */
    case DUPLICATE_CALLBACK = 'duplicate_callback';

    /**
     * مبلغ نامعتبر
     */
    case INVALID_AMOUNT = 'invalid_amount';

    /**
     * توکن نامعتبر
     */
    case INVALID_TOKEN = 'invalid_token';

    /**
     * امضای دیجیتال نامعتبر
     */
    case INVALID_SIGNATURE = 'invalid_signature';

    /**
     * خطای ارتباط با بانک
     */
    case CONNECTION_ERROR = 'connection_error';

    /**
     * عنوان فارسی
     */
    public function label(): string
    {
        return match ($this) {

            self::SUCCESS => 'پرداخت موفق',

            self::FAILED => 'پرداخت ناموفق',

            self::CANCELED => 'انصراف کاربر',

            self::TIMEOUT => 'اتمام زمان پرداخت',

            self::VERIFY_FAILED => 'عدم تایید بانک',

            self::DUPLICATE_CALLBACK => 'Callback تکراری',

            self::INVALID_AMOUNT => 'مبلغ نامعتبر',

            self::INVALID_TOKEN => 'توکن نامعتبر',

            self::INVALID_SIGNATURE => 'امضای دیجیتال نامعتبر',

            self::CONNECTION_ERROR => 'خطای ارتباط با بانک',

        };
    }

    /**
     * لیست برای Select
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $result) => [
                $result->value => $result->label(),
            ])
            ->toArray();
    }
}

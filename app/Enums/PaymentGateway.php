<?php

namespace App\Enums;

enum PaymentGateway: string
{
    /**
     * درگاه تست
     */
    case FAKE = 'fake';

    /**
     * درگاه بانک ملت
     */
    case MELLAT = 'mellat';

    /**
     * درگاه بانک ملی (سداد)
     */
    case SADAD = 'sadad';

    public function label(): string
    {
        return match ($this) {

            self::FAKE => 'درگاه آزمایشی',

            self::MELLAT => 'بانک ملت',

            self::SADAD => 'بانک ملی (سداد)',

        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $gateway) => [
                $gateway->value => $gateway->label(),
            ])
            ->toArray();
    }
}

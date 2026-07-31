<?php

namespace App\Enums;

enum PaymentBank:int
{
    case MELLI = 1;
    case KESHAVARZI = 2;
    case MELLAT = 3;
    case SADERAT = 4;
    case TEJARAT = 5;
    case SEPAH = 6;
    case REFAH = 7;
    case MASKAN = 8;
    case PARSIAN = 9;
    case PASARGAD = 10;
    case SAMAN = 11;
    case AYANDEH = 12;
    case SHAHR = 13;
    case KARAFARIN = 14;
    case EGHTESAD_NOVIN = 15;
    case IRAN_ZAMIN = 16;
    case MEHR_IRAN = 17;
    case POST_BANK = 18;
    case OTHER = 99;

    public function label(): string
    {
        return match ($this) {
            self::MELLI => 'بانک ملی',
            self::KESHAVARZI => 'بانک کشاورزی',
            self::MELLAT => 'بانک ملت',
            self::SADERAT => 'بانک صادرات',
            self::TEJARAT => 'بانک تجارت',
            self::SEPAH => 'بانک سپه',
            self::REFAH => 'بانک رفاه',
            self::MASKAN => 'بانک مسکن',
            self::PARSIAN => 'بانک پارسیان',
            self::PASARGAD => 'بانک پاسارگاد',
            self::SAMAN => 'بانک سامان',
            self::AYANDEH => 'بانک آینده',
            self::SHAHR => 'بانک شهر',
            self::KARAFARIN => 'بانک کارآفرین',
            self::EGHTESAD_NOVIN => 'بانک اقتصاد نوین',
            self::IRAN_ZAMIN => 'بانک ایران زمین',
            self::MEHR_IRAN => 'بانک قرض الحسنه مهر ایران',
            self::POST_BANK => 'پست بانک',
            self::OTHER => 'سایر',
        };
    }
}

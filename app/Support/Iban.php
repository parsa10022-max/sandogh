<?php

namespace App\Support;

class Iban
{
    private const BANKS = [

        '010' => 'بانک مرکزی',
        '011' => 'بانک صنعت و معدن',
        '012' => 'بانک ملت',
        '013' => 'بانک رفاه کارگران',
        '014' => 'بانک مسکن',
        '015' => 'بانک سپه',
        '016' => 'بانک کشاورزی',
        '017' => 'بانک ملی ایران',
        '018' => 'بانک تجارت',
        '019' => 'بانک صادرات ایران',
        '020' => 'بانک توسعه صادرات',
        '021' => 'پست بانک',
        '022' => 'بانک توسعه تعاون',

        '051' => 'مؤسسه اعتباری توسعه',
        '053' => 'بانک کارآفرین',
        '054' => 'بانک پارسیان',
        '055' => 'بانک اقتصاد نوین',
        '056' => 'بانک سامان',
        '057' => 'بانک پاسارگاد',
        '058' => 'بانک سرمایه',
        '059' => 'بانک سینا',
        '060' => 'بانک قرض الحسنه مهر ایران',
        '061' => 'بانک شهر',
        '062' => 'بانک آینده',
        '063' => 'بانک انصار',
        '064' => 'بانک گردشگری',
        '065' => 'بانک حکمت ایرانیان',
        '066' => 'بانک دی',
        '069' => 'بانک ایران زمین',
        '070' => 'بانک رسالت',
        '073' => 'بانک قرض الحسنه مهر ایران',
        '075' => 'بانک مهر اقتصاد',
        '078' => 'بانک خاورمیانه',

    ];
    public static function normalize(?string $iban): ?string
    {
        if (!$iban) {
            return null;
        }

        $iban = strtoupper($iban);
        $iban = preg_replace('/[^A-Z0-9]/', '', $iban);

        if (!str_starts_with($iban, 'IR')) {
            $iban = 'IR' . $iban;
        }

        return $iban;
    }

    public static function digits(?string $iban): string
    {
        if (!$iban) {
            return '';
        }

        return preg_replace('/^IR/i', '', strtoupper($iban));
    }

    public static function format(?string $iban): string
    {
        if (!$iban) {
            return '-';
        }

        $iban = self::normalize($iban);

        return trim(chunk_split($iban, 4, ' '));
    }

    public static function isValid(?string $iban): bool
    {
        $iban = self::normalize($iban);

        return (bool) preg_match('/^IR[0-9]{24}$/', $iban);
    }

    public static function formatDigits(?string $iban): string
    {
        if (blank($iban)) {
            return '';
        }

        $digits = self::digits($iban);

        return trim(
            preg_replace(
                '/(\d{2})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{2})/',
                '$1 $2 $3 $4 $5 $6 $7',
                $digits
            )
        );
    }

    public static function bankName(?string $iban): string
    {
        if (blank($iban)) {
            return '-';
        }

        $iban = self::normalize($iban);

        $bankCode = substr($iban, 4, 3);

        return self::BANKS[$bankCode] ?? 'بانک نامشخص';
    }


}

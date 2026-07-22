<?php

namespace App\Models\Concerns;

use Morilog\Jalali\Jalalian;

trait HasJalaliDates
{
    /**
     * تبدیل تاریخ میلادی به شمسی
     */
    protected function toJalali($date, string $format = 'Y/m/d'): string
    {
        if (empty($date)) {
            return '-';
        }

        return Jalalian::fromDateTime($date)
            ->format($format);
    }
}

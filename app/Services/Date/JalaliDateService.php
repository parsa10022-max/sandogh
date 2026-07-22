<?php

namespace App\Services\Date;

use Carbon\Carbon;
use InvalidArgumentException;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

class JalaliDateService
{
    /**
     * تبدیل تاریخ شمسی به Carbon
     */
    public function toGregorian(string $jalaliDate): Carbon
    {
        return Jalalian::fromFormat(
            'Y/m/d',
            $jalaliDate
        )->toCarbon();
    }

    /**
     * تبدیل Carbon به شمسی
     */
    public function toJalali(Carbon $date): string
    {
        return Jalalian::fromCarbon($date)
            ->format('Y/m/d');
    }

    /**
     * جهت ذخیره در دیتابیس
     */
    public function toDatabase(string $jalaliDate): string
    {
        return $this->toGregorian($jalaliDate)
            ->toDateString();
    }

    /**
     * خواندن از دیتابیس
     */
    public function fromDatabase(string $date): string
    {
        return Jalalian::fromCarbon(
            Carbon::parse($date)
        )->format('Y/m/d');
    }

    /**
     * تاریخ امروز
     */
    public function today(): string
    {
        return Jalalian::fromCarbon(now())
            ->format('Y/m/d');
    }

    /**
     * اولین سررسید
     */
    public function firstDueDate(
        string $registerDate,
        int $interval = 1
    ): string {

        return $this->addMonths(
            $registerDate,
            $interval
        );

    }

    /**
     * بررسی کبیسه بودن
     */
    public function isLeapYear(int $year): bool
    {
        return CalendarUtils::isLeapJalaliYear($year);
    }

    /**
     * تعداد روزهای ماه
     */
    public function daysInMonth(
        int $year,
        int $month
    ): int {

        if ($month < 1 || $month > 12) {

            throw new InvalidArgumentException(
                'شماره ماه نامعتبر است.'
            );

        }

        if ($month <= 6) {
            return 31;
        }

        if ($month <= 11) {
            return 30;
        }

        return $this->isLeapYear($year)
            ? 30
            : 29;
    }

    /**
     * افزودن ماه با حفظ روز قرارداد (Anchor Day)
     */
    public function addMonths(
        string $baseDate,
        int $months = 1,
        ?int $anchorDay = null
    ): string {

        [$year, $month, $day] = $this->parse($baseDate);

        // روز اصلی قرارداد
        $anchorDay ??= $day;

        $month += $months;

        while ($month > 12) {
            $month -= 12;
            $year++;
        }

        while ($month < 1) {
            $month += 12;
            $year--;
        }

        $maxDay = $this->daysInMonth(
            $year,
            $month
        );

        $day = min($anchorDay, $maxDay);

        return $this->format(
            $year,
            $month,
            $day
        );
    }

    /**
     * تولید برنامه اقساط
     */
    public function generateSchedule(
        string $registerDate,
        int $count,
        int $interval = 1
    ): array {

        if ($count <= 0) {
            return [];
        }

        $schedule = [];

        // روز اصلی قرارداد
        [, , $anchorDay] = $this->parse($registerDate);

        for ($number = 1; $number <= $count; $number++) {

            $jalaliDate = $this->addMonths(
                $registerDate,
                $number * $interval,
                $anchorDay
            );

            $schedule[] = [

                'number' => $number,

                // نمایش
                'jalali_date' => $jalaliDate,

                // ذخیره
                'gregorian_date' => $this->toDatabase(
                    $jalaliDate
                ),

            ];

        }

        return $schedule;
    }

    /**
     * تجزیه تاریخ
     */
    private function parse(
        string $jalaliDate
    ): array {

        $parts = explode('/', $jalaliDate);

        if (count($parts) !== 3) {

            throw new InvalidArgumentException(
                'فرمت تاریخ باید Y/m/d باشد.'
            );

        }

        return array_map(
            'intval',
            $parts
        );
    }

    /**
     * ساخت رشته تاریخ
     */
    private function format(
        int $year,
        int $month,
        int $day
    ): string {

        return sprintf(
            '%04d/%02d/%02d',
            $year,
            $month,
            $day
        );
    }
}


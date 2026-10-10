<?php

namespace Tests\Unit\Services\Date;

use App\Services\Date\JalaliDateService;
use Carbon\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class JalaliDateServiceTest extends TestCase
{
    private JalaliDateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new JalaliDateService();
    }

    public function test_it_converts_jalali_date_to_gregorian_and_back(): void
    {
        $jalaliDate = '1405/07/14';

        $gregorian = $this->service->toGregorian($jalaliDate);

        $this->assertInstanceOf(Carbon::class, $gregorian);
        $this->assertSame(
            $jalaliDate,
            $this->service->toJalali($gregorian)
        );
    }

    public function test_it_returns_correct_days_in_jalali_months(): void
    {
        $this->assertSame(31, $this->service->daysInMonth(1405, 1));
        $this->assertSame(31, $this->service->daysInMonth(1405, 6));
        $this->assertSame(30, $this->service->daysInMonth(1405, 7));
        $this->assertSame(29, $this->service->daysInMonth(1405, 12));
    }

    public function test_it_returns_30_days_for_esfand_in_leap_year(): void
    {
        $leapYear = 1403;

        $this->assertTrue(
            $this->service->isLeapYear($leapYear)
        );

        $this->assertSame(
            30,
            $this->service->daysInMonth($leapYear, 12)
        );
    }

    public function test_it_preserves_anchor_day_when_adding_months(): void
    {
        $result = $this->service->addMonths(
            '1405/06/30',
            1
        );

        $this->assertSame(
            '1405/07/30',
            $result
        );
    }

    public function test_it_clamps_anchor_day_to_last_day_of_target_month(): void
    {
        $result = $this->service->addMonths(
            '1405/07/31',
            5
        );

        $this->assertSame(
            '1405/12/29',
            $result
        );
    }

    public function test_it_keeps_original_anchor_day_in_schedule(): void
    {
        $schedule = $this->service->generateSchedule(
            '1405/07/31',
            6
        );

        $this->assertCount(6, $schedule);

        $this->assertSame(1, $schedule[0]['number']);
        $this->assertSame('1405/08/30', $schedule[0]['jalali_date']);

        $this->assertSame(2, $schedule[1]['number']);
        $this->assertSame('1405/09/30', $schedule[1]['jalali_date']);

        $this->assertSame(5, $schedule[4]['number']);
        $this->assertSame('1405/12/29', $schedule[4]['jalali_date']);

        $this->assertSame(6, $schedule[5]['number']);
        $this->assertSame('1406/01/31', $schedule[5]['jalali_date']);

        foreach ($schedule as $item) {
            $this->assertArrayHasKey('number', $item);
            $this->assertArrayHasKey('jalali_date', $item);
            $this->assertArrayHasKey('gregorian_date', $item);
        }
    }

    public function test_it_handles_crossing_year_boundary(): void
    {
        $result = $this->service->addMonths(
            '1405/12/15',
            1
        );

        $this->assertSame(
            '1406/01/15',
            $result
        );
    }

    public function test_it_handles_negative_months(): void
    {
        $result = $this->service->addMonths(
            '1405/02/15',
            -3
        );

        $this->assertSame(
            '1404/11/15',
            $result
        );
    }

    public function test_it_returns_empty_schedule_for_non_positive_count(): void
    {
        $this->assertSame(
            [],
            $this->service->generateSchedule('1405/07/15', 0)
        );

        $this->assertSame(
            [],
            $this->service->generateSchedule('1405/07/15', -1)
        );
    }

    public function test_it_throws_exception_for_invalid_month(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->daysInMonth(1405, 13);
    }

    public function test_first_due_date_uses_month_interval(): void
    {
        $result = $this->service->firstDueDate(
            '1405/07/15',
            3
        );

        $this->assertSame(
            '1405/10/15',
            $result
        );
    }
}

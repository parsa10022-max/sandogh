<?php

namespace Tests\Feature\FundStatistic;

use App\Models\Customer;
use App\Models\FundStatistic;
use App\Services\FundStatistic\FundStatisticService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundStatisticServiceTest extends TestCase
{
    use RefreshDatabase;

    private FundStatisticService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new FundStatisticService();
    }

    public function test_it_returns_default_statistics_when_no_statistic_exists(): void
    {
        Customer::factory()->count(3)->create();

        $result = $this->service->get();

        $this->assertSame(3, $result['members']);
        $this->assertSame(0, $result['paid_loans']);
        $this->assertSame(0, $result['paid_loan_amount']);
        $this->assertSame(0, $result['donations']);
        $this->assertNull($result['statistics_date']);
    }

    public function test_it_returns_existing_statistics(): void
    {
        Customer::factory()->count(5)->create();

        $statistic = FundStatistic::create([
            'paid_loans_count' => 12,
            'paid_loans_amount' => 85000000,
            'donations_count' => 7,
            'statistics_date' => '2026-10-01',
        ]);

        $result = $this->service->get();

        $this->assertSame(5, $result['members']);
        $this->assertSame(12, $result['paid_loans']);
        $this->assertSame(85000000, $result['paid_loan_amount']);
        $this->assertSame(7, $result['donations']);
        $this->assertEquals(
            '2026-10-01',
            $result['statistics_date']->format('Y-m-d')
        );
    }

    public function test_it_creates_statistic_when_no_record_exists(): void
    {
        $result = $this->service->update([
            'paid_loans_count' => 3,
            'paid_loans_amount' => 25000000,
            'donations_count' => 4,
            'statistics_date' => '2026-10-06',
        ]);

        $this->assertInstanceOf(FundStatistic::class, $result);

        $this->assertSame(3, $result->paid_loans_count);
        $this->assertSame(25000000, $result->paid_loans_amount);
        $this->assertSame(4, $result->donations_count);
        $this->assertEquals(
            '2026-10-06',
            $result->statistics_date->format('Y-m-d')
        );

        $this->assertDatabaseHas('fund_statistics', [
            'id' => $result->id,
            'paid_loans_count' => 3,
            'paid_loans_amount' => 25000000,
            'donations_count' => 4,
        ]);
    }

    public function test_it_accumulates_existing_statistics(): void
    {
        $statistic = FundStatistic::create([
            'paid_loans_count' => 10,
            'paid_loans_amount' => 100000000,
            'donations_count' => 5,
            'statistics_date' => '2026-10-01',
        ]);

        $result = $this->service->update([
            'paid_loans_count' => 3,
            'paid_loans_amount' => 25000000,
            'donations_count' => 2,
            'statistics_date' => '2026-10-06',
        ]);

        $this->assertSame($statistic->id, $result->id);
        $this->assertSame(13, $result->paid_loans_count);
        $this->assertSame(125000000, $result->paid_loans_amount);
        $this->assertSame(7, $result->donations_count);
        $this->assertEquals(
            '2026-10-06',
            $result->statistics_date->format('Y-m-d')
        );
    }

    public function test_it_keeps_existing_statistics_date_when_not_provided(): void
    {
        $statistic = FundStatistic::create([
            'paid_loans_count' => 10,
            'paid_loans_amount' => 100000000,
            'donations_count' => 5,
            'statistics_date' => '2026-10-01',
        ]);

        $result = $this->service->update([
            'paid_loans_count' => 2,
            'paid_loans_amount' => 15000000,
            'donations_count' => 1,
        ]);

        $this->assertSame(12, $result->paid_loans_count);
        $this->assertSame(115000000, $result->paid_loans_amount);
        $this->assertSame(6, $result->donations_count);
        $this->assertEquals(
            '2026-10-01',
            $result->statistics_date->format('Y-m-d')
        );
    }
}

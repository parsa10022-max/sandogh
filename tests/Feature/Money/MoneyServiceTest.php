<?php

namespace Tests\Feature\Money;

use App\Services\Money\MoneyService;
use Tests\TestCase;

class MoneyServiceTest extends TestCase
{
    private MoneyService $moneyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moneyService = new MoneyService();
    }

    public function test_it_splits_amount_equally(): void
    {
        $result = $this->moneyService->splitInstallments(900000, 3);

        $this->assertSame([
            ['number' => 1, 'amount' => 300000],
            ['number' => 2, 'amount' => 300000],
            ['number' => 3, 'amount' => 300000],
        ], $result);
    }

    public function test_it_adds_remainder_to_last_installment(): void
    {
        $result = $this->moneyService->splitInstallments(1000, 3);

        $this->assertSame([
            ['number' => 1, 'amount' => 333],
            ['number' => 2, 'amount' => 333],
            ['number' => 3, 'amount' => 334],
        ], $result);
    }

    public function test_it_returns_empty_array_when_installment_count_is_zero(): void
    {
        $this->assertSame(
            [],
            $this->moneyService->splitInstallments(1000, 0)
        );
    }

    public function test_it_returns_empty_array_when_installment_count_is_negative(): void
    {
        $this->assertSame(
            [],
            $this->moneyService->splitInstallments(1000, -2)
        );
    }

    public function test_it_preserves_total_amount(): void
    {
        $loanAmount = 1234567;

        $result = $this->moneyService->splitInstallments($loanAmount, 7);

        $this->assertSame(
            $loanAmount,
            array_sum(array_column($result, 'amount'))
        );
    }
}

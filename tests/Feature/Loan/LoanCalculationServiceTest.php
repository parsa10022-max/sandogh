<?php

namespace Tests\Feature\Loan;

use App\Services\Date\JalaliDateService;
use App\Services\Loan\LoanCalculationService;
use App\Services\Money\MoneyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class LoanCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoanCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LoanCalculationService(
            new JalaliDateService(),
            new MoneyService(),
        );
    }

    public function test_generate_loan_installment_schedule(): void
    {
        $result = $this->service->generate(
            loanAmount: 10000000,
            installmentCount: 10,
            startDate: '1405/07/10',
            interval: 1,
        );

        $this->assertSame(10000000, $result['loan_amount']);
        $this->assertSame(10, $result['installment_count']);
        $this->assertSame(1000000, $result['base_installment_amount']);

        $this->assertCount(10, $result['schedule']);

        $this->assertSame(
            1,
            $result['schedule'][0]['number']
        );

        $this->assertSame(
            10,
            $result['schedule'][9]['number']
        );
    }

    public function test_installment_amounts_are_split_correctly(): void
    {
        $result = $this->service->generate(
            loanAmount: 10000000,
            installmentCount: 10,
            startDate: '1405/07/10',
        );

        foreach ($result['schedule'] as $installment) {
            $this->assertSame(
                1000000,
                $installment['amount']
            );
        }

        $this->assertSame(
            10000000,
            array_sum(
                array_column($result['schedule'], 'amount')
            )
        );
    }

    public function test_remainder_is_added_to_last_installment(): void
    {
        $result = $this->service->generate(
            loanAmount: 10000001,
            installmentCount: 3,
            startDate: '1405/07/10',
        );

        $this->assertSame(
            3333333,
            $result['schedule'][0]['amount']
        );

        $this->assertSame(
            3333333,
            $result['schedule'][1]['amount']
        );

        $this->assertSame(
            3333335,
            $result['schedule'][2]['amount']
        );

        $this->assertSame(
            10000001,
            array_sum(
                array_column($result['schedule'], 'amount')
            )
        );
    }

    public function test_first_and_last_due_dates_are_generated_correctly(): void
    {
        $result = $this->service->generate(
            loanAmount: 12000000,
            installmentCount: 3,
            startDate: '1405/07/10',
            interval: 2,
        );

        $this->assertSame(
            '1405/09/10',
            $result['first_due_date_jalali']
        );

        $this->assertSame(
            '1406/01/10',
            $result['last_due_date_jalali']
        );

        $this->assertSame(
            $result['first_due_date'],
            $result['schedule'][0]['gregorian_date']
        );

        $this->assertSame(
            $result['last_due_date'],
            $result['schedule'][2]['gregorian_date']
        );
    }

    public function test_generate_rejects_invalid_installment_count(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->generate(
            loanAmount: 10000000,
            installmentCount: 0,
            startDate: '1405/07/10',
        );
    }
}

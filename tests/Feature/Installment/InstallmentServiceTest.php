<?php

namespace Tests\Feature\Installment;

use App\Enums\InstallmentStatus;
use App\Models\Customer;
use App\Models\Installment;
use App\Models\LoanType;
use App\Models\User;
use App\Services\Installment\InstallmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstallmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private InstallmentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InstallmentService();
    }

    private function createLoan(): object
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create();

        $loanType = LoanType::create([
            'name' => 'وام ضروری',
            'prefix' => 'VN',
            'status' => 1,
        ]);

        $loanId = DB::table('loans')->insertGetId([
            'customer_id' => $customer->id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 1,
            'loan_amount' => 10000000,
            'installment_amount' => 1000000,
            'installment_count' => 10,
            'installment_interval' => 1,
            'start_date' => '2026-10-01',
            'first_due_date' => '2026-11-01',
            'last_due_date' => '2027-08-01',
            'status' => 1,
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return \App\Models\Loan::findOrFail($loanId);
    }

    public function test_create_installments_for_loan(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $loan = $this->createLoan();

        $schedule = [
            [
                'number' => 1,
                'amount' => 1000000,
                'gregorian_date' => '2026-11-01',
            ],
            [
                'number' => 2,
                'amount' => 1000000,
                'gregorian_date' => '2026-12-01',
            ],
        ];

        $this->service->createForLoan($loan, $schedule);

        $this->assertDatabaseCount('installments', 2);

        $this->assertDatabaseHas('installments', [
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1000000,
            'status' => InstallmentStatus::PENDING->value,
            'created_by' => $user->id,
        ]);
    }

    public function test_get_installments_by_loan_ordered_by_number(): void
    {
        $loan = $this->createLoan();

        Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 2,
            'amount' => 2000000,
            'due_date' => '2026-12-01',
            'status' => InstallmentStatus::PENDING,
        ]);

        Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1000000,
            'due_date' => '2026-11-01',
            'status' => InstallmentStatus::PENDING,
        ]);

        $result = $this->service->getByLoan($loan);

        $this->assertCount(2, $result);
        $this->assertSame(1, $result->first()->installment_number);
        $this->assertSame(2, $result->last()->installment_number);
    }

    public function test_pay_installment(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $loan = $this->createLoan();

        $installment = Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1000000,
            'due_date' => '2026-11-01',
            'status' => InstallmentStatus::PENDING,
        ]);

        $result = $this->service->pay($installment);

        $this->assertSame(
            InstallmentStatus::PAID,
            $result->status
        );

        $this->assertNotNull($result->paid_at);

        $this->assertSame(
            $user->id,
            $result->updated_by
        );
    }

    public function test_cannot_pay_already_paid_installment(): void
    {
        $loan = $this->createLoan();

        $installment = Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1000000,
            'due_date' => '2026-11-01',
            'status' => InstallmentStatus::PAID,
            'paid_at' => now(),
        ]);

        $this->expectException(\DomainException::class);

        $this->service->pay($installment);
    }

    public function test_cancel_payment(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $loan = $this->createLoan();

        $installment = Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1000000,
            'due_date' => '2026-11-01',
            'status' => InstallmentStatus::PAID,
            'paid_at' => now(),
        ]);

        $result = $this->service->cancelPayment($installment);

        $this->assertSame(
            InstallmentStatus::PENDING,
            $result->status
        );

        $this->assertNull($result->paid_at);

        $this->assertSame(
            $user->id,
            $result->updated_by
        );
    }

    public function test_cannot_cancel_payment_for_unpaid_installment(): void
    {
        $loan = $this->createLoan();

        $installment = Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1000000,
            'due_date' => '2026-11-01',
            'status' => InstallmentStatus::PENDING,
        ]);

        $this->expectException(\DomainException::class);

        $this->service->cancelPayment($installment);
    }

    public function test_pending_count_and_paid_amount(): void
    {
        $loan = $this->createLoan();

        Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1000000,
            'due_date' => '2026-11-01',
            'status' => InstallmentStatus::PAID,
            'paid_at' => now(),
        ]);

        Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 2,
            'amount' => 2000000,
            'due_date' => '2026-12-01',
            'status' => InstallmentStatus::PENDING,
        ]);

        Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 3,
            'amount' => 3000000,
            'due_date' => '2027-01-01',
            'status' => InstallmentStatus::PENDING,
        ]);

        $this->assertSame(
            2,
            $this->service->pendingCount($loan)
        );

        $this->assertSame(
            1000000,
            $this->service->paidAmount($loan)
        );
    }

    public function test_overdue_and_upcoming_installments(): void
    {
        $loan = $this->createLoan();

        Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1000000,
            'due_date' => today()->subDays(3),
            'status' => InstallmentStatus::PENDING,
        ]);

        Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 2,
            'amount' => 1000000,
            'due_date' => today()->addDays(3),
            'status' => InstallmentStatus::PENDING,
        ]);

        Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 3,
            'amount' => 1000000,
            'due_date' => today()->addDays(20),
            'status' => InstallmentStatus::PENDING,
        ]);

        $overdue = $this->service->overdue();
        $upcoming = $this->service->upcoming();

        $this->assertCount(1, $overdue);
        $this->assertSame(
            1,
            $overdue->first()->installment_number
        );

        $this->assertCount(1, $upcoming);
        $this->assertSame(
            2,
            $upcoming->first()->installment_number
        );
    }
}

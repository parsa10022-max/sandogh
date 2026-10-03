<?php

namespace Tests\Feature\LoanType;

use App\Enums\LoanTypeStatus;
use App\Models\Customer;
use App\Models\LoanType;
use App\Models\User;
use App\Services\LoanType\LoanTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoanTypeServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoanTypeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LoanTypeService();
    }

    public function test_create_loan_type(): void
    {
        $loanType = $this->service->create([
            'name' => 'وام ضروری',
            'prefix' => 'VN',
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $this->assertDatabaseHas('loan_types', [
            'id' => $loanType->id,
            'name' => 'وام ضروری',
            'prefix' => 'VN',
        ]);
    }

    public function test_change_status(): void
    {
        $loanType = LoanType::create([
            'name' => 'وام ضروری',
            'prefix' => 'VN',
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $result = $this->service->changeStatus($loanType);

        $this->assertSame(
            LoanTypeStatus::INACTIVE,
            $result->status
        );
    }

    public function test_cannot_delete_loan_type_with_loans(): void
    {
        $loanType = LoanType::create([
            'name' => 'وام ضروری',
            'prefix' => 'VN',
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $customer = Customer::factory()->create();
        $user = User::factory()->create();

        DB::table('loans')->insert([
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

        $this->expectException(\RuntimeException::class);

        $this->service->delete($loanType);
    }

    public function test_delete_loan_type_without_loans(): void
    {
        $loanType = LoanType::create([
            'name' => 'وام ضروری',
            'prefix' => 'VN',
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        $result = $this->service->delete($loanType);

        $this->assertTrue($result);

        $this->assertDatabaseMissing('loan_types', [
            'id' => $loanType->id,
        ]);
    }
}

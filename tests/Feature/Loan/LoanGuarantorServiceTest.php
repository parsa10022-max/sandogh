<?php

namespace Tests\Feature\Loan;

use App\Enums\GuarantorType;
use App\Enums\GuaranteeType;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanGuarantor;
use App\Models\LoanType;
use App\Models\User;
use App\Services\Loan\LoanGuarantorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoanGuarantorServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoanGuarantorService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);

        $this->service = app(LoanGuarantorService::class);
    }

    private function createLoan(): Loan
    {
        $customer = Customer::factory()->create();

        $loanType = LoanType::create([
            'name' => 'نوع تست',
            'prefix' => 'T' . strtoupper(Str::random(8)),
            'description' => 'تست',
            'status' => 1,
        ]);

        return Loan::create([
            'customer_id' => $customer->id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'LN-' . strtoupper(Str::random(10)),
            'loan_amount' => 10000000,
            'installment_amount' => 2000000,
            'installment_count' => 5,
            'installment_interval' => 1,
            'start_date' => now(),
            'first_due_date' => now()->addMonth(),
            'last_due_date' => now()->addMonths(5),
            'status' => 1,
            'description' => 'تست',
            'created_by' => $this->user->id,
        ]);
    }

    private function customerGuarantorData(
        Loan $loan,
        int $order = 1
    ): array {
        $customer = Customer::factory()->create();

        return [
            'guarantor_order' => $order,
            'guarantor_type' => GuarantorType::CUSTOMER->value,
            'customer_id' => $customer->id,
            'first_name' => null,
            'last_name' => null,
            'national_code' => null,
            'mobile' => null,
            'guarantee_type' => GuaranteeType::CHECK->value,
            'guarantee_number' => '123456',
            'guarantee_account_number' => null,
            'guarantee_amount' => '10,000,000',
        ];
    }

    public function test_create_customer_guarantor(): void
    {
        $loan = $this->createLoan();

        $guarantor = $this->service->create(
            $loan,
            $this->customerGuarantorData($loan)
        );

        $this->assertInstanceOf(
            LoanGuarantor::class,
            $guarantor
        );

        $this->assertDatabaseHas('loan_guarantors', [
            'id' => $guarantor->id,
            'loan_id' => $loan->id,
            'guarantor_order' => 1,
            'guarantor_type' => GuarantorType::CUSTOMER->value,
            'guarantee_type' => GuaranteeType::CHECK->value,
            'guarantee_amount' => 10000000,
            'guarantee_number' => '123456',
        ]);
    }

    public function test_first_guarantor_must_be_customer(): void
    {
        $loan = $this->createLoan();

        $data = $this->customerGuarantorData($loan);

        $data['guarantor_type'] = GuarantorType::BORROWER->value;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ضامن اول باید عضو صندوق باشد.');

        $this->service->create($loan, $data);
    }

    public function test_borrower_can_only_be_second_guarantor_with_check(): void
    {
        $loan = $this->createLoan();

        $data = [
            'guarantor_order' => 2,
            'guarantor_type' => GuarantorType::BORROWER->value,
            'customer_id' => null,
            'first_name' => null,
            'last_name' => null,
            'national_code' => null,
            'mobile' => null,
            'guarantee_type' => GuaranteeType::CHECK->value,
            'guarantee_number' => '654321',
            'guarantee_account_number' => null,
            'guarantee_amount' => '10,000,000',
        ];

        $guarantor = $this->service->create($loan, $data);

        $this->assertSame(
            GuarantorType::BORROWER,
            $guarantor->guarantor_type
        );

        $this->assertSame(
            GuaranteeType::CHECK,
            $guarantor->guarantee_type
        );
    }

    public function test_borrower_cannot_be_first_guarantor(): void
    {
        $loan = $this->createLoan();

        $data = [
            'guarantor_order' => 1,
            'guarantor_type' => GuarantorType::BORROWER->value,
            'guarantee_type' => GuaranteeType::CHECK->value,
        ];

        $this->expectException(\Exception::class);

        $this->service->create($loan, $data);
    }

    public function test_borrower_requires_check(): void
    {
        $loan = $this->createLoan();

        $data = [
            'guarantor_order' => 2,
            'guarantor_type' => GuarantorType::BORROWER->value,
            'guarantee_type' => GuaranteeType::PROMISSORY_NOTE->value,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('چک صیادی');

        $this->service->create($loan, $data);
    }

    public function test_customer_cannot_guarantee_own_loan(): void
    {
        $loan = $this->createLoan();

        $data = [
            'guarantor_order' => 1,
            'guarantor_type' => GuarantorType::CUSTOMER->value,
            'customer_id' => $loan->customer_id,
            'guarantee_type' => GuaranteeType::CHECK->value,
            'guarantee_number' => '123456',
            'guarantee_amount' => 10000000,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('وام‌گیرنده نمی‌تواند');

        $this->service->create($loan, $data);
    }

    public function test_only_two_guarantors_are_allowed(): void
    {
        $loan = $this->createLoan();

        $this->service->create(
            $loan,
            $this->customerGuarantorData($loan, 1)
        );

        $this->service->create(
            $loan,
            [
                'guarantor_order' => 2,
                'guarantor_type' => GuarantorType::BORROWER->value,
                'guarantee_type' => GuaranteeType::CHECK->value,
                'guarantee_number' => '654321',
                'guarantee_amount' => 10000000,
            ]
        );

        $data = $this->customerGuarantorData($loan, 2);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('فقط دو ضامن');

        $this->service->create($loan, $data);
    }

    public function test_duplicate_guarantor_order_is_rejected(): void
    {
        $loan = $this->createLoan();

        $this->service->create(
            $loan,
            $this->customerGuarantorData($loan, 1)
        );

        $data = $this->customerGuarantorData($loan, 1);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ترتیب ضامن قبلاً');

        $this->service->create($loan, $data);
    }

    public function test_external_guarantor_can_be_created(): void
    {
        $loan = $this->createLoan();

        $data = [
            'guarantor_order' => 2,
            'guarantor_type' => GuarantorType::EXTERNAL->value,
            'customer_id' => null,
            'first_name' => 'علی',
            'last_name' => 'احمدی',
            'national_code' => '1234567890',
            'mobile' => '09120000000',
            'guarantee_type' => GuaranteeType::PROMISSORY_NOTE->value,
            'guarantee_number' => '987654',
            'guarantee_account_number' => '123456',
            'guarantee_amount' => '5,000,000',
        ];

        $guarantor = $this->service->create($loan, $data);

        $this->assertSame(
            GuarantorType::EXTERNAL,
            $guarantor->guarantor_type
        );

        $this->assertSame(
            GuaranteeType::PROMISSORY_NOTE,
            $guarantor->guarantee_type
        );

        $this->assertSame(
            5000000,
            $guarantor->guarantee_amount
        );

        $this->assertNull(
            $guarantor->guarantee_account_number
        );
    }

    public function test_update_guarantor(): void
    {
        $loan = $this->createLoan();

        $guarantor = $this->service->create(
            $loan,
            $this->customerGuarantorData($loan)
        );

        $newCustomer = Customer::factory()->create();

        $updated = $this->service->update(
            $guarantor,
            [
                'guarantor_order' => 1,
                'guarantor_type' => GuarantorType::CUSTOMER->value,
                'customer_id' => $newCustomer->id,
                'guarantee_type' => GuaranteeType::CHECK->value,
                'guarantee_number' => '999999',
                'guarantee_account_number' => null,
                'guarantee_amount' => '20,000,000',
            ]
        );

        $this->assertSame(
            $newCustomer->id,
            $updated->customer_id
        );

        $this->assertSame(
            20000000,
            $updated->guarantee_amount
        );

        $this->assertSame(
            '999999',
            $updated->guarantee_number
        );
    }
}

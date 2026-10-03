<?php

namespace Tests\Feature\Reports;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Enums\LoanTypeStatus;
use App\Enums\PaymentGateway;
use App\Models\Account;
use App\Models\Customer;
use App\Models\DonationPayment;
use App\Models\Installment;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanType;
use App\Models\SavingsTransfer;
use App\Models\User;
use App\Services\Reports\GatewayTransactionsReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GatewayTransactionsReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private GatewayTransactionsReportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(GatewayTransactionsReportService::class);
    }

    private function createCustomer(): Customer
    {
        return Customer::factory()->create();
    }

    private function createUser(Customer $customer): User
    {
        return User::factory()->create([
            'customer_id' => $customer->id,
        ]);
    }

    private function createAccount(Customer $customer): Account
    {
        return Account::query()->create([
            'customer_id' => $customer->id,
            'account_number' => '6111' . str_pad(
                    (string) $customer->id,
                    10,
                    '0',
                    STR_PAD_LEFT
                ),
            'account_type' => AccountType::SAVING,
            'balance' => 0,
            'status' => AccountStatus::ACTIVE,
            'name' => 'حساب پس انداز',
            'opened_date' => now()->toDateString(),
        ]);
    }

    private function createLoanType(): LoanType
    {
        return LoanType::query()->create([
            'name' => 'وام آزمایشی',
            'prefix' => 'TST',
            'status' => LoanTypeStatus::ACTIVE,
        ]);
    }

    private function createLoan(
        Customer $customer,
        LoanType $loanType,
        User $creator
    ): Loan {
        return Loan::query()->create([
            'customer_id' => $customer->id,
            'loan_type_id' => $loanType->id,
            'loan_number' => '100001',
            'loan_amount' => 10_000_000,
            'installment_amount' => 1_000_000,
            'installment_count' => 10,
            'installment_interval' => 1,
            'start_date' => '2026-10-01',
            'first_due_date' => '2026-11-01',
            'last_due_date' => '2027-08-01',
            'status' => LoanStatus::ACTIVE,
            'created_by' => $creator->id,
            'updated_by' => $creator->id,
        ]);
    }

    private function createInstallment(Loan $loan): Installment
    {
        return Installment::query()->create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1_000_000,
            'due_date' => '2026-11-01',
            'status' => InstallmentStatus::PENDING,
        ]);
    }

    public function test_get_transactions_returns_savings_own_transaction(): void
    {
        $customer = $this->createCustomer();
        $user = $this->createUser($customer);
        $account = $this->createAccount($customer);

        $transfer = SavingsTransfer::query()->create([
            'sender_user_id' => $user->id,
            'receiver_customer_id' => $customer->id,
            'account_id' => $account->id,
            'amount' => 2_000_000,
            'status' => 'paid',
            'gateway' => PaymentGateway::FAKE->value,
            'tracking_code' => 'ST-001',
            'bank_transaction_id' => 'BT-001',
            'bank_reference_number' => 'BR-001',
            'paid_at' => Carbon::parse('2026-10-01 10:00:00'),
        ]);

        $transactions = $this->service->getTransactions();

        $this->assertCount(1, $transactions);

        $transaction = $transactions->first();

        $this->assertSame($transfer->id, $transaction['id']);
        $this->assertSame('savings_own', $transaction['type']);
        $this->assertSame(2_000_000, $transaction['amount']);
        $this->assertSame('paid', $transaction['status']);
        $this->assertSame(
            PaymentGateway::FAKE->value,
            $transaction['gateway']
        );
    }

    public function test_get_transactions_returns_savings_other_transaction(): void
    {
        $senderCustomer = $this->createCustomer();
        $senderUser = $this->createUser($senderCustomer);

        $receiverCustomer = $this->createCustomer();
        $receiverUser = $this->createUser($receiverCustomer);
        $receiverAccount = $this->createAccount($receiverCustomer);

        $transfer = SavingsTransfer::query()->create([
            'sender_user_id' => $senderUser->id,
            'receiver_customer_id' => $receiverCustomer->id,
            'account_id' => $receiverAccount->id,
            'amount' => 3_000_000,
            'status' => 'paid',
            'gateway' => PaymentGateway::FAKE->value,
            'tracking_code' => 'ST-002',
            'bank_transaction_id' => 'BT-002',
            'bank_reference_number' => 'BR-002',
            'paid_at' => Carbon::parse('2026-10-02 10:00:00'),
        ]);

        $transactions = $this->service->getTransactions();

        $this->assertCount(1, $transactions);

        $transaction = $transactions->first();

        $this->assertSame($transfer->id, $transaction['id']);
        $this->assertSame('savings_other', $transaction['type']);
        $this->assertSame(3_000_000, $transaction['amount']);
        $this->assertSame('paid', $transaction['status']);
    }

    public function test_get_transactions_uses_paid_at_for_savings_transfer_date_filter(): void
    {
        $customer = $this->createCustomer();
        $user = $this->createUser($customer);
        $account = $this->createAccount($customer);

        $transfer = SavingsTransfer::query()->create([
            'sender_user_id' => $user->id,
            'receiver_customer_id' => $customer->id,
            'account_id' => $account->id,
            'amount' => 1_500_000,
            'status' => 'paid',
            'gateway' => PaymentGateway::FAKE->value,
            'tracking_code' => 'ST-003',
            'created_at' => Carbon::parse('2026-09-01 10:00:00'),
            'paid_at' => Carbon::parse('2026-10-01 10:00:00'),
        ]);

        $transactions = $this->service->getTransactions(
            '2026-10-01',
            '2026-10-01'
        );

        $this->assertCount(1, $transactions);

        $this->assertSame(
            $transfer->id,
            $transactions->first()['id']
        );
    }

    public function test_get_transactions_returns_loan_payment(): void
    {
        $customer = $this->createCustomer();
        $user = $this->createUser($customer);

        $loanType = $this->createLoanType();

        $loan = $this->createLoan(
            $customer,
            $loanType,
            $user
        );

        $installment = $this->createInstallment($loan);

        $payment = LoanPayment::query()->create([
            'loan_id' => $loan->id,
            'installment_id' => $installment->id,
            'user_id' => $user->id,
            'amount' => 1_000_000,
            'gateway' => PaymentGateway::FAKE->value,
            'tracking_code' => 'LP-001',
            'bank_transaction_id' => 'BT-LP-001',
            'bank_reference_number' => 'BR-LP-001',
            'paid_at' => Carbon::parse('2026-10-03 10:00:00'),
        ]);

        $transactions = $this->service->getTransactions();

        $this->assertCount(1, $transactions);

        $transaction = $transactions->first();

        $this->assertSame($payment->id, $transaction['id']);
        $this->assertSame('installment_own', $transaction['type']);
        $this->assertSame(1_000_000, $transaction['amount']);
        $this->assertSame('paid', $transaction['status']);
        $this->assertSame('موفق', $transaction['status_label']);
    }

    public function test_get_transactions_returns_other_customer_loan_payment(): void
    {
        $ownerCustomer = $this->createCustomer();
        $ownerUser = $this->createUser($ownerCustomer);

        $payerCustomer = $this->createCustomer();
        $payerUser = $this->createUser($payerCustomer);

        $loanType = $this->createLoanType();

        $loan = $this->createLoan(
            $ownerCustomer,
            $loanType,
            $ownerUser
        );

        $installment = $this->createInstallment($loan);

        $payment = LoanPayment::query()->create([
            'loan_id' => $loan->id,
            'installment_id' => $installment->id,
            'user_id' => $payerUser->id,
            'amount' => 1_000_000,
            'gateway' => PaymentGateway::FAKE->value,
            'tracking_code' => 'LP-002',
            'bank_transaction_id' => 'BT-LP-002',
            'bank_reference_number' => 'BR-LP-002',
            'paid_at' => Carbon::parse('2026-10-04 10:00:00'),
        ]);

        $transactions = $this->service->getTransactions();

        $this->assertCount(1, $transactions);

        $transaction = $transactions->first();

        $this->assertSame($payment->id, $transaction['id']);
        $this->assertSame('installment_other', $transaction['type']);
        $this->assertSame(1_000_000, $transaction['amount']);
        $this->assertSame('paid', $transaction['status']);
    }

    public function test_get_transactions_returns_system_help(): void
    {
        $customer = $this->createCustomer();
        $account = $this->createAccount($customer);

        $donation = DonationPayment::query()->create([
            'customer_id' => $customer->id,
            'account_id' => $account->id,
            'amount' => 4_000_000,
            'gateway' => PaymentGateway::FAKE->value,
            'tracking_code' => 'DN-001',
            'bank_transaction_id' => 'BT-DN-001',
            'bank_reference_number' => 'BR-DN-001',
            'status' => 1,
            'paid_at' => Carbon::parse('2026-10-05 10:00:00'),
        ]);

        $transactions = $this->service->getTransactions();

        $this->assertCount(1, $transactions);

        $transaction = $transactions->first();

        $this->assertSame($donation->id, $transaction['id']);
        $this->assertSame('system_help', $transaction['type']);
        $this->assertSame(4_000_000, $transaction['amount']);
        $this->assertSame('paid', $transaction['status']);
    }

    public function test_get_transactions_sorts_by_date_descending(): void
    {
        $customer = $this->createCustomer();
        $user = $this->createUser($customer);
        $account = $this->createAccount($customer);

        $older = SavingsTransfer::query()->create([
            'sender_user_id' => $user->id,
            'receiver_customer_id' => $customer->id,
            'account_id' => $account->id,
            'amount' => 1_000_000,
            'status' => 'paid',
            'gateway' => PaymentGateway::FAKE->value,
            'tracking_code' => 'ST-OLD',
            'paid_at' => Carbon::parse('2026-10-01 10:00:00'),
        ]);

        $newer = SavingsTransfer::query()->create([
            'sender_user_id' => $user->id,
            'receiver_customer_id' => $customer->id,
            'account_id' => $account->id,
            'amount' => 2_000_000,
            'status' => 'paid',
            'gateway' => PaymentGateway::FAKE->value,
            'tracking_code' => 'ST-NEW',
            'paid_at' => Carbon::parse('2026-10-02 10:00:00'),
        ]);

        $transactions = $this->service->getTransactions();

        $this->assertCount(2, $transactions);

        $this->assertSame(
            $newer->id,
            $transactions->values()->get(0)['id']
        );

        $this->assertSame(
            $older->id,
            $transactions->values()->get(1)['id']
        );
    }

    public function test_get_summary_calculates_counts_and_amounts(): void
    {
        $transactions = collect([
            [
                'status' => 'paid',
                'amount' => 1_000_000,
            ],
            [
                'status' => 'paid',
                'amount' => 2_000_000,
            ],
            [
                'status' => 'failed',
                'amount' => 3_000_000,
            ],
            [
                'status' => 'pending',
                'amount' => 4_000_000,
            ],
        ]);

        $summary = $this->service->getSummary($transactions);

        $this->assertSame(4, $summary['total_count']);
        $this->assertSame(2, $summary['successful_count']);
        $this->assertSame(1, $summary['failed_count']);
        $this->assertSame(1, $summary['pending_count']);

        $this->assertSame(3_000_000, $summary['successful_amount']);
        $this->assertSame(3_000_000, $summary['failed_amount']);
        $this->assertSame(4_000_000, $summary['pending_amount']);
    }
}

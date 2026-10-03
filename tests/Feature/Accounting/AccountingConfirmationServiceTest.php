<?php

namespace Tests\Feature\Accounting;

use App\Enums\AccountingStatus;
use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Enums\WithdrawalStatus;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\Installment;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanType;
use App\Models\SavingsTransfer;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\Accounting\AccountingConfirmationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingConfirmationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AccountingConfirmationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AccountingConfirmationService::class);
    }

    public function test_confirms_paid_withdrawal(): void
    {
        $user = User::factory()->create();

        $account = $this->createAccount();

        $transaction = $this->createAccountTransaction(
            $account,
            $user
        );

        $withdrawal = Withdrawal::create([
            'account_id' => $account->id,
            'account_transaction_id' => $transaction->id,
            'amount' => 3000000,
            'iban' => 'IR123456789012345678901234',
            'status' => WithdrawalStatus::PAID,
            'paid_by' => $user->id,
            'paid_at' => now(),
        ]);

        $this->actingAs($user);

        $result = $this->service->confirm($withdrawal);

        $this->assertSame(
            AccountingStatus::CONFIRMED,
            $result->accounting_status
        );

        $this->assertSame(
            $user->id,
            $result->accounting_confirmed_by
        );

        $this->assertNotNull(
            $result->accounting_confirmed_at
        );
    }

    public function test_confirms_paid_savings_transfer(): void
    {
        $user = User::factory()->create();

        $customer = Customer::factory()->create();

        $account = $this->createAccount($customer);

        $transfer = SavingsTransfer::create([
            'sender_user_id' => $user->id,
            'receiver_customer_id' => $customer->id,
            'account_id' => $account->id,
            'amount' => 1000000,
            'tracking_code' => 'ST' . uniqid(),
            'gateway' => 'fake',
            'bank_transaction_id' => 'BANK-001',
            'bank_reference_number' => 'REF-001',
            'status' => 'paid',
            'paid_at' => now(),
            'accounting_status' => AccountingStatus::PENDING,
        ]);

        $this->actingAs($user);

        $result = $this->service->confirm($transfer);

        $this->assertSame(
            AccountingStatus::CONFIRMED,
            $result->accounting_status
        );

        $this->assertSame(
            $user->id,
            $result->accounting_confirmed_by
        );

        $this->assertNotNull(
            $result->accounting_confirmed_at
        );
    }

    public function test_confirms_paid_loan_payment(): void
    {
        $user = User::factory()->create();

        $customer = Customer::factory()->create();

        $loanType = LoanType::create([
            'name' => 'وام تست',
            'prefix' => 'TST',
            'status' => 1,
        ]);

        $loan = Loan::create([
            'customer_id' => $customer->id,
            'loan_type_id' => $loanType->id,
            'loan_number' => 'TEST-' . uniqid(),
            'loan_amount' => 10000000,
            'installment_amount' => 1000000,
            'installment_count' => 10,
            'installment_interval' => 1,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths(10)->toDateString(),
            'status' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $installment = Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1000000,
            'due_date' => now()->addMonth()->toDateString(),
            'status' => 0,
        ]);

        $payment = LoanPayment::create([
            'loan_id' => $loan->id,
            'installment_id' => $installment->id,
            'user_id' => $user->id,
            'amount' => 1000000,
            'tracking_code' => 'LP' . uniqid(),
            'gateway' => 'fake',
            'bank_transaction_id' => 'BANK-LP-001',
            'bank_reference_number' => 'REF-LP-001',
            'paid_at' => now(),
            'accounting_status' => AccountingStatus::PENDING,
        ]);

        $this->actingAs($user);

        $result = $this->service->confirm($payment);

        $this->assertSame(
            AccountingStatus::CONFIRMED,
            $result->accounting_status
        );

        $this->assertSame(
            $user->id,
            $result->accounting_confirmed_by
        );

        $this->assertNotNull(
            $result->accounting_confirmed_at
        );
    }

    public function test_rejects_unpaid_withdrawal(): void
    {
        $user = User::factory()->create();

        $account = $this->createAccount();

        $transaction = $this->createAccountTransaction(
            $account,
            $user
        );

        $withdrawal = Withdrawal::create([
            'account_id' => $account->id,
            'account_transaction_id' => $transaction->id,
            'amount' => 3000000,
            'iban' => 'IR123456789012345678901234',
            'status' => WithdrawalStatus::PENDING,
        ]);

        $this->actingAs($user);

        $this->expectException(\DomainException::class);

        $this->expectExceptionMessage(
            'این عملیات هنوز پرداخت نشده است.'
        );

        $this->service->confirm($withdrawal);
    }

    public function test_rejects_unpaid_savings_transfer(): void
    {
        $user = User::factory()->create();

        $customer = Customer::factory()->create();

        $account = $this->createAccount($customer);

        $transfer = SavingsTransfer::create([
            'sender_user_id' => $user->id,
            'receiver_customer_id' => $customer->id,
            'account_id' => $account->id,
            'amount' => 1000000,
            'tracking_code' => 'ST' . uniqid(),
            'gateway' => 'fake',
            'status' => 'pending',
            'accounting_status' => AccountingStatus::PENDING,
        ]);

        $this->actingAs($user);

        $this->expectException(\DomainException::class);

        $this->expectExceptionMessage(
            'این عملیات هنوز پرداخت نشده است.'
        );

        $this->service->confirm($transfer);
    }

    public function test_rejects_already_confirmed_operation(): void
    {
        $user = User::factory()->create();

        $customer = Customer::factory()->create();

        $account = $this->createAccount($customer);

        $transfer = SavingsTransfer::create([
            'sender_user_id' => $user->id,
            'receiver_customer_id' => $customer->id,
            'account_id' => $account->id,
            'amount' => 1000000,
            'tracking_code' => 'ST' . uniqid(),
            'gateway' => 'fake',
            'status' => 'paid',
            'paid_at' => now(),
            'accounting_status' => AccountingStatus::CONFIRMED,
            'accounting_confirmed_by' => $user->id,
            'accounting_confirmed_at' => now(),
        ]);

        $this->actingAs($user);

        $this->expectException(\DomainException::class);

        $this->expectExceptionMessage(
            'این عملیات قبلاً در حسابداری تأیید شده است.'
        );

        $this->service->confirm($transfer);
    }

    public function test_rejects_unknown_operation_model(): void
    {
        $user = User::factory()->create();

        $customer = Customer::factory()->create();

        $account = $this->createAccount($customer);

        $this->actingAs($user);

        $operation = $account;

        $this->expectException(\DomainException::class);

        $this->expectExceptionMessage(
            'این عملیات هنوز پرداخت نشده است.'
        );

        $this->service->confirm($operation);
    }

    private function createAccount(?Customer $customer = null): Account
    {
        $customer ??= Customer::factory()->create();

        return Account::create([
            'customer_id' => $customer->id,
            'account_number' => '6111-' . fake()->unique()->numerify('####'),
            'account_type' => AccountType::SAVING,
            'balance' => 10000000,
            'status' => AccountStatus::ACTIVE,
            'opened_date' => today(),
        ]);
    }

    private function createAccountTransaction(
        Account $account,
        User $user
    ): AccountTransaction {
        return AccountTransaction::create([
            'account_id' => $account->id,
            'transaction_no' => 'AT' . fake()->unique()->numerify('######'),
            'transaction_type' => TransactionType::WITHDRAWAL,
            'transaction_source' => TransactionSource::OPERATOR,
            'amount' => 3000000,
            'balance_before' => 10000000,
            'balance_after' => 7000000,
            'payment_method' => PaymentMethod::CASH,
            'transaction_date' => today(),
            'created_by' => $user->id,
        ]);
    }
}

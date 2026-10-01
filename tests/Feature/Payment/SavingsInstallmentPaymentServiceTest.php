<?php

namespace Tests\Feature\Payment;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Enums\LoanTypeStatus;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Installment;
use App\Models\Loan;
use App\Models\LoanType;
use App\Models\User;
use App\Services\Payment\SavingsInstallmentPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SavingsInstallmentPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(
        string $mobile = '09120000001'
    ): Customer {
        return Customer::factory()->create([
            'mobile' => $mobile,
        ]);
    }

    private function createUserForCustomer(
        Customer $customer
    ): User {
        return User::factory()->create([
            'customer_id' => $customer->id,
        ]);
    }

    private function createSavingAccount(
        Customer $customer,
        int $balance
    ): Account {
        return Account::create([
            'customer_id' => $customer->id,
            'account_number' => '6111' . str_pad(
                    (string) $customer->id,
                    16,
                    '0',
                    STR_PAD_LEFT
                ),
            'account_type' => AccountType::SAVING,
            'balance' => $balance,
            'status' => AccountStatus::ACTIVE,
            'opened_date' => now()->toDateString(),
        ]);
    }

    private function createLoan(
        Customer $customer,
        User $user,
        int $installmentCount = 1,
        int $installmentAmount = 1000000,
    ): Loan {
        $loanType = LoanType::create([
            'name' => 'وام آزمایشی',
            'prefix' => 'T' . str_pad(
                    (string) $customer->id,
                    3,
                    '0',
                    STR_PAD_LEFT
                ),
            'status' => LoanTypeStatus::ACTIVE,
        ]);

        return Loan::create([
            'customer_id' => $customer->id,
            'loan_type_id' => $loanType->id,
            'loan_number' => $customer->id,
            'loan_amount' => $installmentAmount * $installmentCount,
            'installment_amount' => $installmentAmount,
            'installment_count' => $installmentCount,
            'installment_interval' => 1,
            'start_date' => now()->toDateString(),
            'first_due_date' => now()->addMonth()->toDateString(),
            'last_due_date' => now()->addMonths($installmentCount)->toDateString(),
            'status' => LoanStatus::ACTIVE,
            'created_by' => $user->id,
        ]);
    }

    private function createInstallment(
        Loan $loan,
        int $number,
        int $amount,
        ?Carbon $dueDate = null,
        InstallmentStatus $status = InstallmentStatus::PENDING,
    ): Installment {
        return Installment::create([
            'loan_id' => $loan->id,
            'installment_number' => $number,
            'amount' => $amount,
            'due_date' => ($dueDate ?? now()->addMonth())->toDateString(),
            'status' => $status,
        ]);
    }

    public function test_successful_payment_deducts_balance_and_records_everything(): void
    {
        $customer = $this->createCustomer();
        $user = $this->createUserForCustomer($customer);

        $account = $this->createSavingAccount(
            customer: $customer,
            balance: 3000000
        );

        $loan = $this->createLoan(
            customer: $customer,
            user: $user,
            installmentAmount: 1000000
        );

        $installment = $this->createInstallment(
            loan: $loan,
            number: 1,
            amount: 1000000
        );

        $this->actingAs($user);

        $payment = app(
            SavingsInstallmentPaymentService::class
        )->pay($installment);

        $account->refresh();
        $installment->refresh();
        $loan->refresh();

        $this->assertSame(2000000, $account->balance);

        $this->assertSame(
            InstallmentStatus::PAID,
            $installment->status
        );

        $this->assertNotNull($installment->paid_at);

        $this->assertNotNull($payment->id);

        $this->assertSame(
            $installment->id,
            $payment->installment_id
        );

        $this->assertSame(
            $loan->id,
            $payment->loan_id
        );

        $this->assertSame(
            1000000,
            $payment->amount
        );

        $this->assertNotNull($payment->paid_at);

        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $account->id,
            'transaction_type' => TransactionType::INSTALLMENT_PAYMENT->value,
            'transaction_source' => TransactionSource::SYSTEM->value,
            'payment_method' => PaymentMethod::ACCOUNT_BALANCE->value,
            'amount' => 1000000,
            'balance_before' => 3000000,
            'balance_after' => 2000000,
            'created_by' => $user->id,
        ]);

        $this->assertSame(
            LoanStatus::FINISHED,
            $loan->status
        );
    }

    public function test_payment_fails_when_saving_balance_is_insufficient(): void
    {
        $customer = $this->createCustomer();
        $user = $this->createUserForCustomer($customer);

        $this->createSavingAccount(
            customer: $customer,
            balance: 500000
        );

        $loan = $this->createLoan(
            customer: $customer,
            user: $user,
            installmentAmount: 1000000
        );

        $installment = $this->createInstallment(
            loan: $loan,
            number: 1,
            amount: 1000000
        );

        $this->actingAs($user);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(
            'موجودی حساب پس‌انداز برای پرداخت این قسط کافی نیست.'
        );

        app(SavingsInstallmentPaymentService::class)
            ->pay($installment);
    }

    public function test_payment_fails_when_user_is_not_loan_owner(): void
    {
        $owner = $this->createCustomer('09120000001');
        $ownerUser = $this->createUserForCustomer($owner);

        $otherCustomer = $this->createCustomer('09120000002');
        $otherUser = $this->createUserForCustomer($otherCustomer);

        $this->createSavingAccount(
            customer: $owner,
            balance: 3000000
        );

        $loan = $this->createLoan(
            customer: $owner,
            user: $ownerUser,
            installmentAmount: 1000000
        );

        $installment = $this->createInstallment(
            loan: $loan,
            number: 1,
            amount: 1000000
        );

        $this->actingAs($otherUser);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(
            'این قسط متعلق به شما نیست.'
        );

        app(SavingsInstallmentPaymentService::class)
            ->pay($installment);
    }

    public function test_second_installment_cannot_be_paid_before_previous_installment(): void
    {
        $customer = $this->createCustomer();
        $user = $this->createUserForCustomer($customer);

        $this->createSavingAccount(
            customer: $customer,
            balance: 5000000
        );

        $loan = $this->createLoan(
            customer: $customer,
            user: $user,
            installmentCount: 2,
            installmentAmount: 1000000
        );

        $first = $this->createInstallment(
            loan: $loan,
            number: 1,
            amount: 1000000
        );

        $second = $this->createInstallment(
            loan: $loan,
            number: 2,
            amount: 1000000,
            dueDate: now()->addMonths(2)
        );

        $this->actingAs($user);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(
            'ابتدا باید اقساط قبلی پرداخت شوند.'
        );

        app(SavingsInstallmentPaymentService::class)
            ->pay($second);

        $this->assertNotNull($first);
    }

    public function test_already_paid_installment_is_idempotent(): void
    {
        $customer = $this->createCustomer();
        $user = $this->createUserForCustomer($customer);

        $account = $this->createSavingAccount(
            customer: $customer,
            balance: 3000000
        );

        $loan = $this->createLoan(
            customer: $customer,
            user: $user,
            installmentAmount: 1000000
        );

        $installment = $this->createInstallment(
            loan: $loan,
            number: 1,
            amount: 1000000
        );

        $this->actingAs($user);

        $service = app(
            SavingsInstallmentPaymentService::class
        );

        $firstPayment = $service->pay($installment);

        $account->refresh();
        $balanceAfterFirstPayment = $account->balance;

        $secondPayment = $service->pay($installment->fresh());

        $account->refresh();

        $this->assertSame(
            $firstPayment->id,
            $secondPayment->id
        );

        $this->assertSame(
            $balanceAfterFirstPayment,
            $account->balance
        );

        $this->assertDatabaseCount(
            'loan_payments',
            1
        );

        $this->assertDatabaseCount(
            'account_transactions',
            1
        );
    }

    public function test_payment_fails_when_previous_installment_is_overdue(): void
    {
        $customer = $this->createCustomer();
        $user = $this->createUserForCustomer($customer);

        $this->createSavingAccount(
            customer: $customer,
            balance: 5000000
        );

        $loan = $this->createLoan(
            customer: $customer,
            user: $user,
            installmentCount: 2,
            installmentAmount: 1000000
        );

        $this->createInstallment(
            loan: $loan,
            number: 1,
            amount: 1000000,
            dueDate: now()->subMonth(),
            status: InstallmentStatus::OVERDUE
        );

        $second = $this->createInstallment(
            loan: $loan,
            number: 2,
            amount: 1000000,
            dueDate: now()
        );

        $this->actingAs($user);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(
            'ابتدا باید اقساط قبلی پرداخت شوند.'
        );

        app(SavingsInstallmentPaymentService::class)
            ->pay($second);
    }

    public function test_payment_requires_authenticated_user(): void
    {
        $customer = $this->createCustomer();

        $loanUser = $this->createUserForCustomer($customer);

        $loan = $this->createLoan(
            customer: $customer,
            user: $loanUser,
            installmentAmount: 1000000
        );

        $installment = $this->createInstallment(
            loan: $loan,
            number: 1,
            amount: 1000000
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(
            'کاربر وارد سیستم نشده است.'
        );

        app(SavingsInstallmentPaymentService::class)
            ->pay($installment);
    }
}

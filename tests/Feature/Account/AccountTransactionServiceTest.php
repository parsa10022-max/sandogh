<?php

namespace Tests\Feature\Account;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Services\Account\AccountTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomer(): Customer
    {
        return Customer::factory()->active()->create();
    }

    private function createAccount(
        ?Customer $customer = null,
        int $balance = 0
    ): Account {
        $customer ??= $this->createCustomer();

        return Account::create([
            'customer_id' => $customer->id,
            'account_number' => '6111-' . fake()->unique()->numerify('####'),
            'account_type' => AccountType::SAVING,
            'balance' => $balance,
            'status' => AccountStatus::ACTIVE,
            'opened_date' => today(),
        ]);
    }

    public function test_creates_account_transaction_with_all_financial_data(): void
    {
        $account = $this->createAccount(balance: 1_000_000);

        $service = app(AccountTransactionService::class);

        $transaction = $service->create(
            account: $account,
            type: TransactionType::DEPOSIT,
            source: TransactionSource::OPERATOR,
            paymentMethod: PaymentMethod::CASH,
            amount: 500_000,
            balanceBefore: 1_000_000,
            balanceAfter: 1_500_000,
            description: 'تست ثبت تراکنش',
        );

        $this->assertInstanceOf(
            AccountTransaction::class,
            $transaction
        );

        $this->assertSame(
            $account->id,
            $transaction->account_id
        );

        $this->assertSame(
            TransactionType::DEPOSIT,
            $transaction->transaction_type
        );

        $this->assertSame(
            TransactionSource::OPERATOR,
            $transaction->transaction_source
        );

        $this->assertSame(
            PaymentMethod::CASH,
            $transaction->payment_method
        );

        $this->assertSame(500_000, $transaction->amount);
        $this->assertSame(1_000_000, $transaction->balance_before);
        $this->assertSame(1_500_000, $transaction->balance_after);
        $this->assertSame(
            'تست ثبت تراکنش',
            $transaction->description
        );

        $this->assertMatchesRegularExpression(
            '/^AT\d{8}\d{6}$/',
            $transaction->transaction_no
        );

        $this->assertDatabaseHas('account_transactions', [
            'id' => $transaction->id,
            'account_id' => $account->id,
            'amount' => 500_000,
            'balance_before' => 1_000_000,
            'balance_after' => 1_500_000,
            'transaction_type' => TransactionType::DEPOSIT->value,
            'transaction_source' => TransactionSource::OPERATOR->value,
            'payment_method' => PaymentMethod::CASH->value,
            'description' => 'تست ثبت تراکنش',
        ]);
    }

    public function test_creates_multiple_transactions_with_unique_transaction_numbers(): void
    {
        $account = $this->createAccount(balance: 2_000_000);

        $service = app(AccountTransactionService::class);

        $first = $service->create(
            account: $account,
            type: TransactionType::DEPOSIT,
            source: TransactionSource::OPERATOR,
            paymentMethod: PaymentMethod::CASH,
            amount: 500_000,
            balanceBefore: 2_000_000,
            balanceAfter: 2_500_000,
        );

        $second = $service->create(
            account: $account,
            type: TransactionType::WITHDRAWAL,
            source: TransactionSource::OPERATOR,
            paymentMethod: PaymentMethod::CASH,
            amount: 200_000,
            balanceBefore: 2_500_000,
            balanceAfter: 2_300_000,
        );

        $this->assertNotSame(
            $first->transaction_no,
            $second->transaction_no
        );

        $this->assertDatabaseCount(
            'account_transactions',
            2
        );
    }

    public function test_creates_transaction_without_optional_fields(): void
    {
        $account = $this->createAccount(balance: 800_000);

        $service = app(AccountTransactionService::class);

        $transaction = $service->create(
            account: $account,
            type: TransactionType::DEPOSIT,
            source: TransactionSource::SYSTEM,
            paymentMethod: PaymentMethod::GATEWAY,
            amount: 300_000,
            balanceBefore: 800_000,
            balanceAfter: 1_100_000,
        );

        $this->assertDatabaseHas('account_transactions', [
            'id' => $transaction->id,
            'account_id' => $account->id,
            'created_by' => null,
            'description' => null,
        ]);
    }
}

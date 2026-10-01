<?php

namespace Tests\Feature\Account;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Enums\WithdrawalStatus;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\Withdrawal;
use App\Services\Account\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AccountServiceTest extends TestCase
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

    public function test_deposit_increases_balance_and_creates_transaction(): void
    {
        $account = $this->createAccount(balance: 100000);

        $transaction = app(AccountService::class)->deposit(
            account: $account,
            amount: 50000,
            paymentMethod: PaymentMethod::CASH,
        );

        $account->refresh();

        $this->assertSame(150000, $account->balance);

        $this->assertSame($account->id, $transaction->account_id);
        $this->assertSame(TransactionType::DEPOSIT, $transaction->transaction_type);
        $this->assertSame(TransactionSource::OPERATOR, $transaction->transaction_source);
        $this->assertSame(PaymentMethod::CASH, $transaction->payment_method);
        $this->assertSame(50000, $transaction->amount);
        $this->assertSame(100000, $transaction->balance_before);
        $this->assertSame(150000, $transaction->balance_after);

        $this->assertDatabaseHas('account_transactions', [
            'id' => $transaction->id,
            'account_id' => $account->id,
            'amount' => 50000,
            'balance_before' => 100000,
            'balance_after' => 150000,
            'transaction_type' => TransactionType::DEPOSIT->value,
        ]);
    }

    public function test_deposit_rejects_amount_below_minimum(): void
    {
        $account = $this->createAccount();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('حداقل مبلغ واریز ۵۰,۰۰۰ ریال است.');

        app(AccountService::class)->deposit(
            account: $account,
            amount: 49999,
            paymentMethod: PaymentMethod::CASH,
        );
    }

    public function test_withdraw_decreases_balance_and_creates_pending_withdrawal(): void
    {
        $customer = $this->createCustomer();

        $customer->update([
            'iban' => 'IR123456789012345678901234',
        ]);

        $account = $this->createAccount(
            customer: $customer,
            balance: 1000000
        );

        $withdrawal = app(AccountService::class)->withdraw(
            account: $account,
            amount: 500000,
            paymentMethod: PaymentMethod::BANK_TRANSFER,
        );

        $account->refresh();
        $withdrawal->refresh();

        $this->assertSame(500000, $account->balance);

        $this->assertSame($account->id, $withdrawal->account_id);
        $this->assertSame(500000, $withdrawal->amount);
        $this->assertSame(
            WithdrawalStatus::PENDING,
            $withdrawal->status
        );
        $this->assertSame(
            'IR123456789012345678901234',
            $withdrawal->iban
        );

        $this->assertNotNull($withdrawal->account_transaction_id);

        $this->assertDatabaseHas('account_transactions', [
            'id' => $withdrawal->account_transaction_id,
            'account_id' => $account->id,
            'transaction_type' => TransactionType::WITHDRAWAL->value,
            'amount' => 500000,
            'balance_before' => 1000000,
            'balance_after' => 500000,
        ]);
    }

    public function test_withdraw_rejects_amount_below_minimum(): void
    {
        $account = $this->createAccount(balance: 1000000);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('حداقل مبلغ برداشت ۵۰۰,۰۰۰ ریال است.');

        app(AccountService::class)->withdraw(
            account: $account,
            amount: 499999,
            paymentMethod: PaymentMethod::BANK_TRANSFER,
        );
    }

    public function test_withdraw_rejects_insufficient_balance(): void
    {
        $customer = $this->createCustomer();

        $customer->update([
            'iban' => 'IR123456789012345678901234',
        ]);

        $account = $this->createAccount(
            customer: $customer,
            balance: 500000
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('موجودی حساب برای برداشت کافی نیست.');

        app(AccountService::class)->withdraw(
            account: $account,
            amount: 600000,
            paymentMethod: PaymentMethod::BANK_TRANSFER,
        );

        $account->refresh();

        $this->assertSame(500000, $account->balance);
    }

    public function test_withdraw_requires_iban(): void
    {
        $account = $this->createAccount(balance: 1000000);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('شماره شبا برای برداشت مشخص نشده است.');

        app(AccountService::class)->withdraw(
            account: $account,
            amount: 500000,
            paymentMethod: PaymentMethod::BANK_TRANSFER,
        );
    }

    public function test_cancel_pending_withdrawal_returns_money_to_account(): void
    {
        $customer = $this->createCustomer();

        $customer->update([
            'iban' => 'IR123456789012345678901234',
        ]);

        $account = $this->createAccount(
            customer: $customer,
            balance: 1000000
        );

        $service = app(AccountService::class);

        $withdrawal = $service->withdraw(
            account: $account,
            amount: 500000,
            paymentMethod: PaymentMethod::BANK_TRANSFER,
        );

        $cancelled = $service->cancel(
            withdrawal: $withdrawal,
            customerId: $customer->id,
        );

        $account->refresh();
        $cancelled->refresh();

        $this->assertSame(1000000, $account->balance);
        $this->assertSame(
            WithdrawalStatus::CANCELLED,
            $cancelled->status
        );

        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $account->id,
            'transaction_type' => TransactionType::DEPOSIT->value,
            'amount' => 500000,
            'balance_before' => 500000,
            'balance_after' => 1000000,
            'description' => 'برگشت مبلغ برداشت لغو شده',
        ]);
    }

    public function test_cancel_withdrawal_rejects_other_customer(): void
    {
        $customer = $this->createCustomer();
        $otherCustomer = $this->createCustomer();

        $customer->update([
            'iban' => 'IR123456789012345678901234',
        ]);

        $account = $this->createAccount(
            customer: $customer,
            balance: 1000000
        );

        $withdrawal = app(AccountService::class)->withdraw(
            account: $account,
            amount: 500000,
            paymentMethod: PaymentMethod::BANK_TRANSFER,
        );

        $this->expectException(ValidationException::class);

        app(AccountService::class)->cancel(
            withdrawal: $withdrawal,
            customerId: $otherCustomer->id,
        );
    }

    public function test_reject_withdrawal_returns_money_to_account(): void
    {
        $customer = $this->createCustomer();

        $customer->update([
            'iban' => 'IR123456789012345678901234',
        ]);

        $account = $this->createAccount(
            customer: $customer,
            balance: 1000000
        );

        $service = app(AccountService::class);

        $withdrawal = $service->withdraw(
            account: $account,
            amount: 500000,
            paymentMethod: PaymentMethod::BANK_TRANSFER,
        );

        $rejected = $service->rejectWithdrawal($withdrawal);

        $account->refresh();
        $rejected->refresh();

        $this->assertSame(1000000, $account->balance);
        $this->assertSame(
            WithdrawalStatus::REJECTED,
            $rejected->status
        );

        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $account->id,
            'transaction_type' => TransactionType::DEPOSIT->value,
            'amount' => 500000,
            'balance_before' => 500000,
            'balance_after' => 1000000,
            'description' => 'برگشت مبلغ برداشت رد شده',
        ]);
    }

    public function test_adjust_balance_updates_balance_and_creates_adjustment_transaction(): void
    {
        $account = $this->createAccount(balance: 1000000);

        $transaction = app(AccountService::class)->adjustBalance(
            account: $account,
            newBalance: 1200000,
            description: 'اصلاح تست',
        );

        $account->refresh();

        $this->assertSame(1200000, $account->balance);

        $this->assertSame(
            TransactionType::ADJUSTMENT,
            $transaction->transaction_type
        );

        $this->assertSame(1000000, $transaction->balance_before);
        $this->assertSame(1200000, $transaction->balance_after);
        $this->assertSame(200000, $transaction->amount);
        $this->assertSame('اصلاح تست', $transaction->description);
    }

    public function test_adjust_balance_rejects_negative_balance(): void
    {
        $account = $this->createAccount(balance: 1000000);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('موجودی نمی‌تواند منفی باشد.');

        app(AccountService::class)->adjustBalance(
            account: $account,
            newBalance: -1,
        );
    }

    public function test_adjust_balance_rejects_same_balance(): void
    {
        $account = $this->createAccount(balance: 1000000);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'موجودی جدید با موجودی فعلی یکسان است.'
        );

        app(AccountService::class)->adjustBalance(
            account: $account,
            newBalance: 1000000,
        );
    }
}

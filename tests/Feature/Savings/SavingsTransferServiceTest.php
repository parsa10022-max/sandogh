<?php

namespace Tests\Feature\Savings;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\PaymentIntent;
use App\Models\User;
use App\Models\SavingsTransfer;
use App\Services\Savings\SavingsTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SavingsTransferServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createSavingAccount(
        Customer $customer,
        int $balance = 0,
        AccountStatus $status = AccountStatus::ACTIVE
    ): Account {
        return Account::create([
            'customer_id' => $customer->id,
            'account_number' => '6111' . str_pad(
                    (string) fake()->unique()->numberBetween(1, 999999999999),
                    12,
                    '0',
                    STR_PAD_LEFT
                ),
            'account_type' => AccountType::SAVING,
            'balance' => $balance,
            'status' => $status,
            'name' => 'حساب پس‌انداز',
            'opened_date' => now()->toDateString(),
        ]);
    }

    public function test_start_payment_creates_transfer_and_payment_intent(): void
    {
        $sender = User::factory()->create();
        $receiver = Customer::factory()->create();

        $account = $this->createSavingAccount($receiver);

        $this->actingAs($sender);

        $service = app(SavingsTransferService::class);

        $result = $service->startPayment($receiver, 5_000_000);

        $transfer = $result['transfer'];
        $paymentIntent = $result['payment_intent'];

        $this->assertInstanceOf(
            SavingsTransfer::class,
            $transfer
        );

        $this->assertSame(
            $sender->id,
            $transfer->sender_user_id
        );

        $this->assertSame(
            $receiver->id,
            $transfer->receiver_customer_id
        );

        $this->assertSame(
            $account->id,
            $transfer->account_id
        );

        $this->assertSame(
            5_000_000,
            (int) $transfer->amount
        );

        $this->assertSame(
            'pending',
            $transfer->status
        );

        $this->assertNotNull(
            $transfer->tracking_code
        );

        $this->assertInstanceOf(
            PaymentIntent::class,
            $paymentIntent
        );

        $this->assertSame(
            'savings_transfer',
            'savings_transfer',
            $paymentIntent->payment_type
        );
   

        $this->assertSame(
            $transfer->id,
            (int) $paymentIntent->reference_id
        );

        $this->assertSame(
            5_000_000,
            (int) $paymentIntent->amount
        );

        $this->assertNotNull(
            $paymentIntent->gateway_token
        );
    }

    public function test_start_payment_rejects_non_positive_amount(): void
    {
        $sender = User::factory()->create();
        $receiver = Customer::factory()->create();

        $this->actingAs($sender);

        $service = app(SavingsTransferService::class);

        $this->expectException(\DomainException::class);

        $service->startPayment($receiver, 0);
    }

    public function test_start_payment_rejects_receiver_without_active_saving_account(): void
    {
        $sender = User::factory()->create();
        $receiver = Customer::factory()->create();

        $this->createSavingAccount(
            $receiver,
            0,
            AccountStatus::BLOCKED
        );

        $this->actingAs($sender);

        $service = app(SavingsTransferService::class);

        $this->expectException(\DomainException::class);

        $service->startPayment($receiver, 5_000_000);
    }

    public function test_successful_payment_increases_receiver_balance(): void
    {
        $sender = User::factory()->create();
        $receiver = Customer::factory()->create();

        $account = $this->createSavingAccount(
            $receiver,
            10_000_000
        );

        $this->actingAs($sender);

        $service = app(SavingsTransferService::class);

        $result = $service->startPayment(
            $receiver,
            5_000_000
        );

        $paymentIntent = $result['payment_intent'];

        $transfer = $service->verifyPayment([
            'payment_intent_id' => $paymentIntent->id,
            'token' => $paymentIntent->gateway_token,
        ]);

        $account->refresh();

        $this->assertSame(
            15_000_000,
            (int) $account->balance
        );

        $this->assertSame(
            'paid',
            $transfer->status
        );

        $this->assertNotNull(
            $transfer->paid_at
        );

        $this->assertNotNull(
            $transfer->bank_transaction_id
        );

        $this->assertNotNull(
            $transfer->bank_reference_number
        );
    }

    public function test_successful_payment_creates_account_transaction(): void
    {
        $sender = User::factory()->create();
        $receiver = Customer::factory()->create();

        $account = $this->createSavingAccount(
            $receiver,
            10_000_000
        );

        $this->actingAs($sender);

        $service = app(SavingsTransferService::class);

        $result = $service->startPayment(
            $receiver,
            5_000_000
        );

        $paymentIntent = $result['payment_intent'];

        $service->verifyPayment([
            'payment_intent_id' => $paymentIntent->id,
            'token' => $paymentIntent->gateway_token,
        ]);

        $transaction = AccountTransaction::query()
            ->where('account_id', $account->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($transaction);

        $this->assertSame(
            TransactionType::DEPOSIT,
            $transaction->transaction_type
        );

        $this->assertSame(
            TransactionSource::ONLINE,
            $transaction->transaction_source
        );

        $this->assertSame(
            PaymentMethod::GATEWAY,
            $transaction->payment_method
        );

        $this->assertSame(
            5_000_000,
            (int) $transaction->amount
        );

        $this->assertSame(
            10_000_000,
            (int) $transaction->balance_before
        );

        $this->assertSame(
            15_000_000,
            (int) $transaction->balance_after
        );
    }

    public function test_invalid_callback_token_does_not_change_balance(): void
    {
        $sender = User::factory()->create();
        $receiver = Customer::factory()->create();

        $account = $this->createSavingAccount(
            $receiver,
            10_000_000
        );

        $this->actingAs($sender);

        $service = app(SavingsTransferService::class);

        $result = $service->startPayment(
            $receiver,
            5_000_000
        );

        $paymentIntent = $result['payment_intent'];

        $this->expectException(\DomainException::class);

        try {
            $service->verifyPayment([
                'payment_intent_id' => $paymentIntent->id,
                'token' => 'invalid-token',
            ]);
        } finally {
            $account->refresh();

            $this->assertSame(
                10_000_000,
                (int) $account->balance
            );
        }
    }

    public function test_tampered_payment_intent_amount_is_rejected(): void
    {
        $sender = User::factory()->create();
        $receiver = Customer::factory()->create();

        $account = $this->createSavingAccount(
            $receiver,
            10_000_000
        );

        $this->actingAs($sender);

        $service = app(SavingsTransferService::class);

        $result = $service->startPayment(
            $receiver,
            5_000_000
        );

        $paymentIntent = $result['payment_intent'];

        $paymentIntent->update([
            'amount' => 9_000_000,
        ]);

        $this->expectException(\DomainException::class);

        try {
            $service->verifyPayment([
                'payment_intent_id' => $paymentIntent->id,
                'token' => $paymentIntent->gateway_token,
            ]);
        } finally {
            $account->refresh();

            $this->assertSame(
                10_000_000,
                (int) $account->balance
            );
        }
    }

    public function test_replayed_successful_callback_does_not_duplicate_transaction(): void
    {
        $sender = User::factory()->create();
        $receiver = Customer::factory()->create();

        $account = $this->createSavingAccount(
            $receiver,
            10_000_000
        );

        $this->actingAs($sender);

        $service = app(SavingsTransferService::class);

        $result = $service->startPayment(
            $receiver,
            5_000_000
        );

        $paymentIntent = $result['payment_intent'];

        $callback = [
            'payment_intent_id' => $paymentIntent->id,
            'token' => $paymentIntent->gateway_token,
        ];

        $service->verifyPayment($callback);

        $transactionCountBefore = AccountTransaction::query()
            ->where('account_id', $account->id)
            ->count();

        $service->verifyPayment($callback);

        $account->refresh();

        $transactionCountAfter = AccountTransaction::query()
            ->where('account_id', $account->id)
            ->count();

        $this->assertSame(
            15_000_000,
            (int) $account->balance
        );

        $this->assertSame(
            $transactionCountBefore,
            $transactionCountAfter
        );
    }

    public function test_payment_to_another_customer_creates_paid_transfer(): void
    {
        $sender = User::factory()->create();

        $receiver = Customer::factory()->create();

        $account = $this->createSavingAccount(
            $receiver,
            20_000_000
        );

        $this->actingAs($sender);

        $service = app(SavingsTransferService::class);

        $result = $service->startPayment(
            $receiver,
            7_000_000
        );

        $paymentIntent = $result['payment_intent'];

        $transfer = $service->verifyPayment([
            'payment_intent_id' => $paymentIntent->id,
            'token' => $paymentIntent->gateway_token,
        ]);

        $account->refresh();

        $this->assertSame(
            'paid',
            $transfer->status
        );

        $this->assertSame(
            $sender->id,
            $transfer->sender_user_id
        );

        $this->assertSame(
            $receiver->id,
            $transfer->receiver_customer_id
        );

        $this->assertSame(
            27_000_000,
            (int) $account->balance
        );
    }

    public function test_transactions_returns_authenticated_users_saving_transactions(): void
    {
        $user = User::factory()->create();

        $account = $this->createSavingAccount(
            $user->customer,
            10_000_000
        );

        DB::table('account_transactions')->insert([
            'account_id' => $account->id,
            'transaction_no' => 'AT-TEST-000001',
            'transaction_type' => TransactionType::DEPOSIT->value,
            'transaction_source' => TransactionSource::ONLINE->value,
            'amount' => 5_000_000,
            'balance_before' => 5_000_000,
            'balance_after' => 10_000_000,
            'payment_method' => PaymentMethod::GATEWAY->value,
            'transaction_date' => now()->toDateString(),
            'created_by' => null,
            'description' => 'تست',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);

        $service = app(SavingsTransferService::class);

        $transactions = $service->transactions();

        $this->assertSame(
            1,
            $transactions->total()
        );

        $this->assertSame(
            5_000_000,
            (int) $transactions->first()->amount
        );

        $this->assertSame(
            $account->id,
            $transactions->first()->account_id
        );
    }
}

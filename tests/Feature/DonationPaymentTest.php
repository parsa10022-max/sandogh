<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentGateway;
use App\Models\Account;
use App\Models\Customer;
use App\Models\DonationPayment;
use App\Models\PaymentIntent;
use App\Services\Donation\DonationPaymentService;
use App\Services\Payment\Gateways\GatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class DonationPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'payment.gateway' => 'fake',
        ]);
    }

    private function createAccount(int $balance = 0): Account
    {
        $customer = Customer::factory()->create();

        return Account::create([
            'customer_id' => $customer->id,
            'account_number' => '6111' . fake()->unique()->numerify('##########'),
            'account_type' => AccountType::SAVING,
            'balance' => $balance,
            'status' => AccountStatus::ACTIVE,
            'name' => 'حساب پس‌انداز',
            'opened_date' => now()->toDateString(),
        ]);
    }

    private function createPaymentIntent(
        DonationPayment $payment,
        string $paymentType = 'donation_public',
        string $token = 'donation-token'
    ): PaymentIntent {
        return PaymentIntent::create([
            'payment_type' => $paymentType,
            'reference_id' => $payment->id,
            'payer_user_id' => null,
            'amount' => $payment->amount,
            'tracking_code' => $payment->tracking_code,
            'gateway' => PaymentGateway::from(config('payment.gateway')),
            'gateway_token' => $token,
            'status' => 'redirected',
            'expires_at' => now()->addMinutes(30),
        ]);
    }

    private function createDonation(
        Account $account,
        int $amount = 500000
    ): DonationPayment {
        return DonationPayment::create([
            'customer_id' => null,
            'donor_name' => 'خیر',
            'donor_mobile' => '09120000000',
            'account_id' => $account->id,
            'amount' => $amount,
            'tracking_code' => 'DON-' . strtoupper(fake()->unique()->bothify('##########')),
            'gateway' => PaymentGateway::from(config('payment.gateway')),
            'status' => 0,
        ]);
    }

    public function test_successful_donation_payment_increases_account_balance(): void
    {
        $account = $this->createAccount(1000000);

        $payment = $this->createDonation($account, 500000);

        $intent = $this->createPaymentIntent($payment);

        $gateway = Mockery::mock(GatewayInterface::class);

        $gateway
            ->shouldReceive('verify')
            ->once()
            ->andReturn([
                'success' => true,
                'transaction_id' => 'TX-DON-001',
                'reference_number' => 'REF-DON-001',
            ]);

        $this->app->instance(GatewayInterface::class, $gateway);

        $service = app(DonationPaymentService::class);

        $result = $service->verifyPayment([
            'payment_intent_id' => (string) $intent->id,
            'token' => 'donation-token',
            'payment_type' => 'donation_public',
            'reference_id' => $payment->id,
        ]);

        $this->assertSame($payment->id, $result->id);

        $this->assertDatabaseHas('donation_payments', [
            'id' => $payment->id,
            'status' => 1,
            'bank_transaction_id' => 'TX-DON-001',
            'bank_reference_number' => 'REF-DON-001',
        ]);

        $this->assertDatabaseHas('payment_intents', [
            'id' => $intent->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'balance' => 1500000,
        ]);

        $this->assertDatabaseCount('account_transactions', 1);
    }

    public function test_invalid_donation_callback_token_does_not_change_balance(): void
    {
        $account = $this->createAccount(1000000);

        $payment = $this->createDonation($account, 500000);

        $intent = $this->createPaymentIntent(
            $payment,
            'donation_public',
            'correct-token'
        );

        $gateway = Mockery::mock(GatewayInterface::class);

        $gateway
            ->shouldNotReceive('verify');

        $this->app->instance(GatewayInterface::class, $gateway);

        $service = app(DonationPaymentService::class);

        $this->expectException(\DomainException::class);

        try {
            $service->verifyPayment([
                'payment_intent_id' => (string) $intent->id,
                'token' => 'wrong-token',
                'payment_type' => 'donation_public',
                'reference_id' => $payment->id,
            ]);
        } finally {
            $this->assertDatabaseHas('accounts', [
                'id' => $account->id,
                'balance' => 1000000,
            ]);

            $this->assertDatabaseHas('donation_payments', [
                'id' => $payment->id,
                'status' => 0,
            ]);
        }
    }

    public function test_donation_with_tampered_payment_intent_amount_is_rejected(): void
    {
        $account = $this->createAccount(1000000);

        $payment = $this->createDonation($account, 500000);

        $intent = $this->createPaymentIntent($payment);

        $intent->update([
            'amount' => 900000,
        ]);

        $gateway = Mockery::mock(GatewayInterface::class);

        $gateway
            ->shouldNotReceive('verify');

        $this->app->instance(GatewayInterface::class, $gateway);

        $service = app(DonationPaymentService::class);

        $this->expectException(\DomainException::class);

        try {
            $service->verifyPayment([
                'payment_intent_id' => (string) $intent->id,
                'token' => 'donation-token',
                'payment_type' => 'donation_public',
                'reference_id' => $payment->id,
            ]);
        } finally {
            $this->assertDatabaseHas('accounts', [
                'id' => $account->id,
                'balance' => 1000000,
            ]);

            $this->assertDatabaseHas('donation_payments', [
                'id' => $payment->id,
                'status' => 0,
            ]);
        }
    }

    public function test_replayed_successful_donation_callback_does_not_create_duplicate_transaction(): void
    {
        $account = $this->createAccount(1000000);

        $payment = $this->createDonation($account, 500000);

        $intent = $this->createPaymentIntent($payment);

        $gateway = Mockery::mock(GatewayInterface::class);

        $gateway
            ->shouldReceive('verify')
            ->once()
            ->andReturn([
                'success' => true,
                'transaction_id' => 'TX-DON-REPLAY',
                'reference_number' => 'REF-DON-REPLAY',
            ]);

        $this->app->instance(GatewayInterface::class, $gateway);

        $service = app(DonationPaymentService::class);

        $callback = [
            'payment_intent_id' => (string) $intent->id,
            'token' => 'donation-token',
            'payment_type' => 'donation_public',
            'reference_id' => $payment->id,
        ];

        $service->verifyPayment($callback);

        $service->verifyPayment($callback);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'balance' => 1500000,
        ]);

        $this->assertDatabaseHas('donation_payments', [
            'id' => $payment->id,
            'status' => 1,
        ]);

        $this->assertDatabaseHas('payment_intents', [
            'id' => $intent->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseCount('account_transactions', 1);
    }
}

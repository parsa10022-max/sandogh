<?php

namespace Tests\Feature\Donation;

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

class DonationPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'payment.gateway' => 'fake',
        ]);
    }

    private function createAccount(
        int $balance = 0,
        AccountStatus $status = AccountStatus::ACTIVE
    ): Account {
        $customer = Customer::factory()->create();

        return Account::create([
            'customer_id' => $customer->id,
            'account_number' => '6111' . fake()->unique()->numerify('##########'),
            'account_type' => AccountType::SAVING,
            'balance' => $balance,
            'status' => $status,
            'name' => 'حساب پس‌انداز',
            'opened_date' => now()->toDateString(),
        ]);
    }

    private function createDonation(
        Account $account,
        int $amount = 500000,
        ?Customer $customer = null,
        int $status = 0
    ): DonationPayment {
        return DonationPayment::create([
            'customer_id' => $customer?->id,
            'donor_name' => 'خیر',
            'donor_mobile' => '09120000000',
            'account_id' => $account->id,
            'amount' => $amount,
            'tracking_code' => 'DON-' . strtoupper(
                    fake()->unique()->bothify('##########')
                ),
            'gateway' => PaymentGateway::from(config('payment.gateway')),
            'status' => $status,
        ]);
    }

    public function test_start_payment_creates_payment_and_payment_intent_and_redirects_to_gateway(): void
    {
        $account = $this->createAccount();

        $gateway = Mockery::mock(GatewayInterface::class);

        $gateway
            ->shouldReceive('request')
            ->once()
            ->with(Mockery::on(function (array $data): bool {
                return
                    isset($data['payment_intent_id']) &&
                    $data['payment_type'] === 'donation_public' &&
                    $data['amount'] === 500000 &&
                    isset($data['tracking_code']) &&
                    $data['callback_url'] === route('payments.callback');
            }))
            ->andReturn([
                'success' => true,
                'token' => 'donation-gateway-token',
                'payment_intent_id' => null,
                'redirect_url' => 'https://gateway.test/pay',
            ]);

        $this->app->instance(GatewayInterface::class, $gateway);

        $service = app(DonationPaymentService::class);

        $result = $service->startPayment(
            customer: null,
            account: $account,
            amount: 500000,
            donorName: 'خیر',
            donorMobile: '09120000000',
            paymentType: 'donation_public',
        );

        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('payment_intent', $result);
        $this->assertArrayHasKey('gateway', $result);

        $payment = $result['payment'];
        $intent = $result['payment_intent'];

        $this->assertInstanceOf(DonationPayment::class, $payment);
        $this->assertInstanceOf(PaymentIntent::class, $intent);

        $this->assertSame(500000, (int) $payment->amount);
        $this->assertSame('donation_public', $intent->payment_type);
        $this->assertSame($payment->id, (int) $intent->reference_id);
        $this->assertSame(500000, (int) $intent->amount);
        $this->assertSame('donation-gateway-token', $intent->gateway_token);
        $this->assertSame('redirected', $intent->status);

        $this->assertDatabaseHas('donation_payments', [
            'id' => $payment->id,
            'status' => 0,
            'amount' => 500000,
        ]);

        $this->assertDatabaseHas('payment_intents', [
            'id' => $intent->id,
            'payment_type' => 'donation_public',
            'reference_id' => $payment->id,
            'amount' => 500000,
            'gateway_token' => 'donation-gateway-token',
            'status' => 'redirected',
        ]);
    }

    public function test_start_payment_rejects_non_positive_amount(): void
    {
        $account = $this->createAccount();

        $gateway = Mockery::mock(GatewayInterface::class);
        $gateway->shouldNotReceive('request');

        $this->app->instance(GatewayInterface::class, $gateway);

        $service = app(DonationPaymentService::class);

        $this->expectException(\DomainException::class);

        $service->startPayment(
            customer: null,
            account: $account,
            amount: 0,
            donorName: 'خیر',
            donorMobile: '09120000000',
            paymentType: 'donation_public',
        );

        $this->assertDatabaseCount('donation_payments', 0);
        $this->assertDatabaseCount('payment_intents', 0);
    }

    public function test_start_payment_rejects_inactive_account(): void
    {
        $account = $this->createAccount(
            balance: 0,
            status: AccountStatus::BLOCKED
        );

        $gateway = Mockery::mock(GatewayInterface::class);
        $gateway->shouldNotReceive('request');

        $this->app->instance(GatewayInterface::class, $gateway);

        $service = app(DonationPaymentService::class);

        $this->expectException(\DomainException::class);

        $service->startPayment(
            customer: null,
            account: $account,
            amount: 500000,
            donorName: 'خیر',
            donorMobile: '09120000000',
            paymentType: 'donation_public',
        );

        $this->assertDatabaseCount('donation_payments', 0);
        $this->assertDatabaseCount('payment_intents', 0);
    }

    public function test_start_payment_rejects_invalid_payment_type(): void
    {
        $account = $this->createAccount();

        $gateway = Mockery::mock(GatewayInterface::class);
        $gateway->shouldNotReceive('request');

        $this->app->instance(GatewayInterface::class, $gateway);

        $service = app(DonationPaymentService::class);

        $this->expectException(\DomainException::class);

        $service->startPayment(
            customer: null,
            account: $account,
            amount: 500000,
            donorName: 'خیر',
            donorMobile: '09120000000',
            paymentType: 'invalid_type',
        );

        $this->assertDatabaseCount('donation_payments', 0);
        $this->assertDatabaseCount('payment_intents', 0);
    }

    public function test_start_payment_gateway_failure_marks_payment_and_intent_as_failed(): void
    {
        $account = $this->createAccount();

        $gateway = Mockery::mock(GatewayInterface::class);

        $gateway
            ->shouldReceive('request')
            ->once()
            ->andReturn([
                'success' => false,
                'message' => 'Gateway failed',
            ]);

        $this->app->instance(GatewayInterface::class, $gateway);

        $service = app(DonationPaymentService::class);

        $this->expectException(\DomainException::class);

        try {
            $service->startPayment(
                customer: null,
                account: $account,
                amount: 500000,
                donorName: 'خیر',
                donorMobile: '09120000000',
                paymentType: 'donation_public',
            );
        } finally {
            $payment = DonationPayment::query()->latest('id')->first();
            $intent = PaymentIntent::query()->latest('id')->first();

            $this->assertNotNull($payment);
            $this->assertNotNull($intent);

            $this->assertSame(2, (int) $payment->status);
            $this->assertSame('failed', $intent->status);
        }
    }

    public function test_send_to_gateway_creates_payment_intent_and_redirects_successfully(): void
    {
        $account = $this->createAccount();

        $payment = $this->createDonation(
            account: $account,
            amount: 750000,
        );

        $gateway = Mockery::mock(GatewayInterface::class);

        $gateway
            ->shouldReceive('request')
            ->once()
            ->with(Mockery::on(function (array $data) use ($payment): bool {
                return
                    $data['payment_type'] === 'donation_public' &&
                    (int) $data['reference_id'] === $payment->id &&
                    (int) $data['amount'] === 750000 &&
                    $data['tracking_code'] === $payment->tracking_code &&
                    $data['callback_url'] === route('payments.callback');
            }))
            ->andReturn([
                'success' => true,
                'token' => 'gateway-token-send',
                'redirect_url' => 'https://gateway.test/pay',
            ]);

        $this->app->instance(GatewayInterface::class, $gateway);

        $service = app(DonationPaymentService::class);

        $result = $service->sendToGateway(
            payment: $payment,
            paymentType: 'donation_public',
        );

        $intent = $result['payment_intent'];

        $this->assertInstanceOf(PaymentIntent::class, $intent);
        $this->assertSame($payment->id, (int) $intent->reference_id);
        $this->assertSame(750000, (int) $intent->amount);
        $this->assertSame(
            $payment->tracking_code,
            $intent->tracking_code
        );
        $this->assertSame(
            'gateway-token-send',
            $intent->gateway_token
        );
        $this->assertSame('redirected', $intent->status);

        $this->assertDatabaseHas('payment_intents', [
            'id' => $intent->id,
            'reference_id' => $payment->id,
            'amount' => 750000,
            'gateway_token' => 'gateway-token-send',
            'status' => 'redirected',
        ]);
    }

    public function test_send_to_gateway_rejects_already_completed_payment(): void
    {
        $account = $this->createAccount();

        $payment = $this->createDonation(
            account: $account,
            amount: 500000,
            status: 1,
        );

        $gateway = Mockery::mock(GatewayInterface::class);
        $gateway->shouldNotReceive('request');

        $this->app->instance(GatewayInterface::class, $gateway);

        $service = app(DonationPaymentService::class);

        $this->expectException(\DomainException::class);

        $service->sendToGateway(
            payment: $payment,
            paymentType: 'donation_public',
        );

        $this->assertDatabaseCount('payment_intents', 0);
    }
}

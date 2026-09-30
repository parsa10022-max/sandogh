<?php

namespace Tests\Feature\Payment;

use App\Enums\PaymentGateway;
use App\Models\PaymentIntent;
use App\Models\User;
use App\Services\Payment\PaymentIntentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PaymentIntentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentIntentService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PaymentIntentService::class);

        $this->user = User::factory()->create();
    }

    public function test_create_creates_pending_payment_intent(): void
    {
        $intent = $this->createIntent();

        $this->assertSame('pending', $intent->status);
        $this->assertSame(1000000, $intent->amount);
        $this->assertSame('installment', $intent->payment_type);
        $this->assertSame(123, $intent->reference_id);
        $this->assertSame(
            $this->user->id,
            $intent->payer_user_id
        );
    }

    public function test_pending_intent_can_be_marked_redirected(): void
    {
        $intent = $this->createIntent();

        $intent = $this->service->markRedirected(
            $intent,
            'gateway-token-123'
        );

        $this->assertSame(
            'redirected',
            $intent->status
        );

        $this->assertSame(
            'gateway-token-123',
            $intent->gateway_token
        );
    }

    public function test_redirected_intent_can_be_marked_verifying(): void
    {
        $intent = $this->createRedirectedIntent();

        $intent = $this->service->markVerifying(
            $intent
        );

        $this->assertSame(
            'verifying',
            $intent->status
        );
    }

    public function test_verifying_intent_can_be_marked_paid(): void
    {
        $intent = $this->createRedirectedIntent();

        $intent = $this->service->markVerifying(
            $intent
        );

        $intent = $this->service->markPaid(
            $intent,
            'transaction-123',
            'reference-123'
        );

        $this->assertSame(
            'paid',
            $intent->status
        );

        $this->assertSame(
            'transaction-123',
            $intent->gateway_transaction_id
        );

        $this->assertSame(
            'reference-123',
            $intent->gateway_reference_number
        );

        $this->assertNotNull(
            $intent->paid_at
        );
    }

    public function test_redirected_intent_can_be_marked_failed(): void
    {
        $intent = $this->createRedirectedIntent();

        $intent = $this->service->markFailed(
            $intent
        );

        $this->assertSame(
            'failed',
            $intent->status
        );
    }

    public function test_expired_intent_is_marked_expired(): void
    {
        $intent = $this->createRedirectedIntent();

        Carbon::setTestNow(
            now()->addMinutes(31)
        );

        try {
            $this->expectException(
                RuntimeException::class
            );

            $this->service->markVerifying(
                $intent
            );
        } finally {
            Carbon::setTestNow();
        }

        $intent->refresh();

        $this->assertSame(
            'expired',
            $intent->status
        );
    }

    public function test_validate_for_payment_marks_expired_intent_as_expired(): void
    {
        $intent = $this->createRedirectedIntent();

        Carbon::setTestNow(
            now()->addMinutes(31)
        );

        try {
            $this->expectException(
                RuntimeException::class
            );

            $this->service->validateForPayment(
                intent: $intent,
                paymentType: 'installment',
                referenceId: 123,
                amount: 1000000,
                gateway: $this->gateway(),
            );
        } finally {
            Carbon::setTestNow();
        }

        $intent->refresh();

        $this->assertSame(
            'expired',
            $intent->status
        );
    }

    public function test_paid_intent_cannot_be_marked_paid_again(): void
    {
        $intent = $this->createPaidIntent();

        $this->expectException(
            RuntimeException::class
        );

        $this->service->markPaid(
            $intent,
            'transaction-456',
            'reference-456'
        );
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $intent = $this->createIntent();

        $this->expectException(
            RuntimeException::class
        );

        $this->service->markPaid(
            $intent
        );
    }

    public function test_validate_rejects_wrong_payment_type(): void
    {
        $intent = $this->createRedirectedIntent();

        $this->expectException(
            RuntimeException::class
        );

        $this->service->validateForPayment(
            intent: $intent,
            paymentType: 'savings_transfer',
            referenceId: 123,
            amount: 1000000,
            gateway: $this->gateway(),
        );
    }

    public function test_validate_rejects_wrong_reference_id(): void
    {
        $intent = $this->createRedirectedIntent();

        $this->expectException(
            RuntimeException::class
        );

        $this->service->validateForPayment(
            intent: $intent,
            paymentType: 'installment',
            referenceId: 999,
            amount: 1000000,
            gateway: $this->gateway(),
        );
    }

    public function test_validate_rejects_wrong_amount(): void
    {
        $intent = $this->createRedirectedIntent();

        $this->expectException(
            RuntimeException::class
        );

        $this->service->validateForPayment(
            intent: $intent,
            paymentType: 'installment',
            referenceId: 123,
            amount: 2000000,
            gateway: $this->gateway(),
        );
    }

    public function test_validate_rejects_wrong_gateway(): void
    {
        $intent = $this->createRedirectedIntent();

        $wrongGateway = collect(
            PaymentGateway::cases()
        )->first(
            fn (PaymentGateway $gateway) =>
                $gateway !== $this->gateway()
        );

        if (! $wrongGateway) {
            $this->markTestSkipped(
                'حداقل دو Gateway برای این تست لازم است.'
            );
        }

        $this->expectException(
            RuntimeException::class
        );

        $this->service->validateForPayment(
            intent: $intent,
            paymentType: 'installment',
            referenceId: 123,
            amount: 1000000,
            gateway: $wrongGateway,
        );
    }

    public function test_validate_accepts_valid_payment_intent(): void
    {
        $intent = $this->createRedirectedIntent();

        $this->service->validateForPayment(
            intent: $intent,
            paymentType: 'installment',
            referenceId: 123,
            amount: 1000000,
            gateway: $this->gateway(),
        );

        $this->assertSame(
            'redirected',
            $intent->status
        );
    }

    public function test_paid_intent_cannot_be_expired(): void
    {
        $intent = $this->createPaidIntent();

        $this->expectException(
            RuntimeException::class
        );

        $this->service->markExpired(
            $intent
        );
    }

    private function createIntent(): PaymentIntent
    {
        return $this->service->create(
            paymentType: 'installment',
            referenceId: 123,
            amount: 1000000,
            trackingCode: 'LP' . now()->format('YmdHisv') . uniqid(),
            gateway: $this->gateway(),
            payerUserId: $this->user->id,
            expiresInMinutes: 30,
        );
    }

    private function createRedirectedIntent(): PaymentIntent
    {
        $intent = $this->createIntent();

        return $this->service->markRedirected(
            $intent,
            'gateway-token-' . uniqid()
        );
    }

    private function createPaidIntent(): PaymentIntent
    {
        $intent = $this->createRedirectedIntent();

        $intent = $this->service->markVerifying(
            $intent
        );

        return $this->service->markPaid(
            $intent,
            'transaction-' . uniqid(),
            'reference-' . uniqid()
        );
    }

    private function gateway(): PaymentGateway
    {
        return PaymentGateway::from(
            config('payment.gateway')
        );
    }
}

<?php

namespace Tests\Feature\Payment\Gateways;

use App\Enums\FakeGatewayResult;
use App\Services\Payment\Gateways\FakeGateway;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FakeGatewayTest extends TestCase
{
    private FakeGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakeGateway();
    }

    public function test_request_returns_success_with_token_and_payment_intent_id(): void
    {
        URL::shouldReceive('route')
            ->once()
            ->withArgs(function (
                string $name,
                array $parameters
            ) {
                return $name === 'payments.fake'
                    && $parameters['payment_intent_id'] === 123
                    && $parameters['token'] !== null;
            })
            ->andReturn('/payments/fake?test=1');

        $result = $this->gateway->request([
            'payment_intent_id' => 123,
            'payment_type' => 'installment',
            'reference_id' => 456,
            'loan_id' => 10,
            'installment_id' => 20,
            'amount' => 1000000,
            'tracking_code' => 'LP14050101000001',
        ]);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['token']);
        $this->assertSame(123, $result['payment_intent_id']);
        $this->assertSame(
            '/payments/fake?test=1',
            $result['redirect_url']
        );
    }

    public function test_request_rejects_missing_payment_intent_id(): void
    {
        $result = $this->gateway->request([
            'payment_type' => 'installment',
            'reference_id' => 456,
            'amount' => 1000000,
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame(
            'شناسه Payment Intent ارسال نشده است.',
            $result['message']
        );
    }

    public function test_request_uses_existing_gateway_token(): void
    {
        URL::shouldReceive('route')
            ->once()
            ->withArgs(function (
                string $name,
                array $parameters
            ) {
                return $name === 'payments.fake'
                    && $parameters['payment_intent_id'] === 123
                    && $parameters['token'] === 'fixed-token-123';
            })
            ->andReturn('/payments/fake?test=1');

        $result = $this->gateway->request([
            'payment_intent_id' => 123,
            'gateway_token' => 'fixed-token-123',
            'amount' => 1000000,
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(
            'fixed-token-123',
            $result['token']
        );
        $this->assertSame(
            '/payments/fake?test=1',
            $result['redirect_url']
        );
    }

    public function test_verify_rejects_missing_token(): void
    {
        $result = $this->gateway->verify([
            'payment_intent_id' => 123,
            'result' => FakeGatewayResult::SUCCESS->value,
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame(
            'توکن پرداخت ارسال نشده است.',
            $result['message']
        );
    }

    public function test_verify_rejects_missing_payment_intent_id(): void
    {
        $result = $this->gateway->verify([
            'token' => 'test-token',
            'result' => FakeGatewayResult::SUCCESS->value,
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame(
            'شناسه Payment Intent ارسال نشده است.',
            $result['message']
        );
    }

    public function test_verify_success_returns_transaction_information(): void
    {
        $result = $this->gateway->verify([
            'token' => 'test-token',
            'payment_intent_id' => 123,
            'result' => FakeGatewayResult::SUCCESS->value,
        ]);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['transaction_id']);
        $this->assertNotEmpty($result['reference_number']);
        $this->assertSame(
            '603799******1234',
            $result['card_number']
        );
        $this->assertSame(
            'پرداخت با موفقیت انجام شد.',
            $result['message']
        );
    }

    public function test_verify_failed_result(): void
    {
        $result = $this->gateway->verify([
            'token' => 'test-token',
            'payment_intent_id' => 123,
            'result' => FakeGatewayResult::FAILED->value,
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame(
            'پرداخت ناموفق بود.',
            $result['message']
        );
    }

    public function test_verify_canceled_result(): void
    {
        $result = $this->gateway->verify([
            'token' => 'test-token',
            'payment_intent_id' => 123,
            'result' => FakeGatewayResult::CANCELED->value,
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame(
            'کاربر پرداخت را لغو کرد.',
            $result['message']
        );
    }

    public function test_verify_timeout_result(): void
    {
        $result = $this->gateway->verify([
            'token' => 'test-token',
            'payment_intent_id' => 123,
            'result' => FakeGatewayResult::TIMEOUT->value,
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame(
            'مهلت پرداخت به پایان رسید.',
            $result['message']
        );
    }

    public function test_verify_failed_verification_result(): void
    {
        $result = $this->gateway->verify([
            'token' => 'test-token',
            'payment_intent_id' => 123,
            'result' => FakeGatewayResult::VERIFY_FAILED->value,
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame(
            'بانک پرداخت را تأیید نکرد.',
            $result['message']
        );
    }

    public function test_verify_invalid_token_result(): void
    {
        $result = $this->gateway->verify([
            'token' => 'test-token',
            'payment_intent_id' => 123,
            'result' => FakeGatewayResult::INVALID_TOKEN->value,
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame(
            'توکن پرداخت نامعتبر است.',
            $result['message']
        );
    }

    public function test_verify_invalid_signature_result(): void
    {
        $result = $this->gateway->verify([
            'token' => 'test-token',
            'payment_intent_id' => 123,
            'result' => FakeGatewayResult::INVALID_SIGNATURE->value,
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame(
            'امضای دیجیتال نامعتبر است.',
            $result['message']
        );
    }

    public function test_verify_connection_error_result(): void
    {
        $result = $this->gateway->verify([
            'token' => 'test-token',
            'payment_intent_id' => 123,
            'result' => FakeGatewayResult::CONNECTION_ERROR->value,
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame(
            'ارتباط با درگاه بانکی برقرار نشد.',
            $result['message']
        );
    }

    public function test_unknown_result_defaults_to_success(): void
    {
        $result = $this->gateway->verify([
            'token' => 'test-token',
            'payment_intent_id' => 123,
            'result' => 'unknown-result',
        ]);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['transaction_id']);
        $this->assertNotEmpty($result['reference_number']);
    }
}

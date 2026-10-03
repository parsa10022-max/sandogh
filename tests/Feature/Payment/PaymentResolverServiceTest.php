<?php

namespace Tests\Feature\Payment;

use App\Models\DonationPayment;
use App\Models\LoanPayment;
use App\Models\SavingsTransfer;
use App\Services\Donation\DonationPaymentService;
use App\Services\Payment\PaymentResolverService;
use App\Services\Payment\PaymentService;
use App\Services\Savings\SavingsTransferService;
use Mockery;
use Tests\TestCase;

class PaymentResolverServiceTest extends TestCase
{
    private PaymentService $loanPaymentService;

    private SavingsTransferService $savingsTransferService;

    private DonationPaymentService $donationPaymentService;

    private PaymentResolverService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loanPaymentService = Mockery::mock(
            PaymentService::class
        );

        $this->savingsTransferService = Mockery::mock(
            SavingsTransferService::class
        );

        $this->donationPaymentService = Mockery::mock(
            DonationPaymentService::class
        );

        $this->service = new PaymentResolverService(
            $this->loanPaymentService,
            $this->savingsTransferService,
            $this->donationPaymentService,
        );
    }

    public function test_resolves_savings_transfer_payment(): void
    {
        $callbackData = [
            'payment_type' => 'savings_transfer',
            'tracking_code' => 'ST123456',
        ];

        $expected = new SavingsTransfer();

        $this->savingsTransferService
            ->shouldReceive('verifyPayment')
            ->once()
            ->with($callbackData)
            ->andReturn($expected);

        $result = $this->service->verify($callbackData);

        $this->assertSame($expected, $result);
    }

    public function test_resolves_installment_payment(): void
    {
        $callbackData = [
            'payment_type' => 'installment',
            'tracking_code' => 'LP123456',
        ];

        $expected = new LoanPayment();

        $this->loanPaymentService
            ->shouldReceive('verifyPayment')
            ->once()
            ->with($callbackData)
            ->andReturn($expected);

        $result = $this->service->verify($callbackData);

        $this->assertSame($expected, $result);
    }

    public function test_resolves_customer_installment_payment(): void
    {
        $callbackData = [
            'payment_type' => 'installment_customer',
            'tracking_code' => 'LP123456',
        ];

        $expected = new LoanPayment();

        $this->loanPaymentService
            ->shouldReceive('verifyPayment')
            ->once()
            ->with($callbackData)
            ->andReturn($expected);

        $result = $this->service->verify($callbackData);

        $this->assertSame($expected, $result);
    }

    public function test_resolves_other_customer_installment_payment(): void
    {
        $callbackData = [
            'payment_type' => 'installment_other',
            'tracking_code' => 'LP123456',
        ];

        $expected = new LoanPayment();

        $this->loanPaymentService
            ->shouldReceive('verifyPayment')
            ->once()
            ->with($callbackData)
            ->andReturn($expected);

        $result = $this->service->verify($callbackData);

        $this->assertSame($expected, $result);
    }

    public function test_resolves_public_donation_payment(): void
    {
        $callbackData = [
            'payment_type' => 'donation_public',
            'tracking_code' => 'DP123456',
        ];

        $expected = new DonationPayment();

        $this->donationPaymentService
            ->shouldReceive('verifyPayment')
            ->once()
            ->with($callbackData)
            ->andReturn($expected);

        $result = $this->service->verify($callbackData);

        $this->assertSame($expected, $result);
    }

    public function test_resolves_customer_donation_payment(): void
    {
        $callbackData = [
            'payment_type' => 'donation_customer',
            'tracking_code' => 'DP123456',
        ];

        $expected = new DonationPayment();

        $this->donationPaymentService
            ->shouldReceive('verifyPayment')
            ->once()
            ->with($callbackData)
            ->andReturn($expected);

        $result = $this->service->verify($callbackData);

        $this->assertSame($expected, $result);
    }

    public function test_rejects_unknown_payment_type(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->expectExceptionMessage(
            'نوع پرداخت مشخص نیست.'
        );

        $this->service->verify([
            'payment_type' => 'unknown',
        ]);
    }

    public function test_rejects_missing_payment_type(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->expectExceptionMessage(
            'نوع پرداخت مشخص نیست.'
        );

        $this->service->verify([]);
    }
}

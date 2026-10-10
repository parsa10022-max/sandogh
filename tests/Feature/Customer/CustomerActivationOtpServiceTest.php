<?php

namespace Tests\Feature\Customer;

use App\Enums\CustomerActivationOtpStatus;
use App\Models\Customer;
use App\Models\CustomerActivationOtp;
use App\Services\CustomerActivationOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerActivationOtpServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_a_pending_hashed_otp(): void
    {
        $customer = Customer::factory()->create();

        $request = Request::create(
            '/activate-account',
            'POST',
            [],
            [],
            [],
            [
                'REMOTE_ADDR' => '127.0.0.1',
                'HTTP_USER_AGENT' => 'PHPUnit',
            ]
        );

        $service = app(CustomerActivationOtpService::class);

        $otp = $service->generate($customer, $request);

        $this->assertSame($customer->id, $otp->customer_id);
        $this->assertSame($customer->mobile, $otp->mobile);
        $this->assertSame(CustomerActivationOtpStatus::PENDING, $otp->status);
        $this->assertSame(0, $otp->attempts);
        $this->assertNotNull($otp->expires_at);
        $this->assertNotNull($otp->ip_address);
        $this->assertNotNull($otp->user_agent);
        $this->assertNotSame('100000', $otp->code);
    }

    public function test_it_verifies_a_correct_otp(): void
    {
        $customer = Customer::factory()->create();

        $code = '123456';

        $otp = CustomerActivationOtp::create([
            'customer_id' => $customer->id,
            'mobile' => $customer->mobile,
            'code' => Hash::make($code),
            'status' => CustomerActivationOtpStatus::PENDING,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(2),
        ]);

        $service = app(CustomerActivationOtpService::class);

        $result = $service->verify($customer, $code);

        $otp->refresh();

        $this->assertTrue($result);
        $this->assertSame(
            CustomerActivationOtpStatus::VERIFIED,
            $otp->status
        );
        $this->assertSame(1, $otp->attempts);
        $this->assertNotNull($otp->verified_at);
    }

    public function test_it_rejects_wrong_otp_and_increments_attempts(): void
    {
        $customer = Customer::factory()->create();

        $otp = CustomerActivationOtp::create([
            'customer_id' => $customer->id,
            'mobile' => $customer->mobile,
            'code' => Hash::make('123456'),
            'status' => CustomerActivationOtpStatus::PENDING,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(2),
        ]);

        $service = app(CustomerActivationOtpService::class);

        $result = $service->verify($customer, '654321');

        $otp->refresh();

        $this->assertFalse($result);
        $this->assertSame(1, $otp->attempts);
        $this->assertSame(
            CustomerActivationOtpStatus::PENDING,
            $otp->status
        );
    }

    public function test_it_cancels_otp_after_max_attempts(): void
    {
        $customer = Customer::factory()->create();

        $otp = CustomerActivationOtp::create([
            'customer_id' => $customer->id,
            'mobile' => $customer->mobile,
            'code' => Hash::make('123456'),
            'status' => CustomerActivationOtpStatus::PENDING,
            'attempts' => 5,
            'expires_at' => now()->addMinutes(2),
        ]);

        $service = app(CustomerActivationOtpService::class);

        $result = $service->verify($customer, '123456');

        $otp->refresh();

        $this->assertFalse($result);
        $this->assertSame(
            CustomerActivationOtpStatus::CANCELLED,
            $otp->status
        );
        $this->assertNotNull($otp->cancelled_at);
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Enums\UserOtpStatus;
use App\Enums\UserOtpType;
use App\Models\User;
use App\Models\UserOtp;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_is_cancelled_immediately_after_fifth_wrong_attempt(): void
    {
        $user = User::factory()->create();

        $otp = UserOtp::create([
            'user_id' => $user->id,
            'mobile' => $user->mobile,
            'code' => Hash::make('123456'),
            'type' => UserOtpType::LOGIN,
            'status' => UserOtpStatus::PENDING,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(2),
        ]);

        $service = app(OtpService::class);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->assertFalse(
                $service->verify($user, '000000', UserOtpType::LOGIN)
            );
        }

        $otp->refresh();

        $this->assertSame(5, $otp->attempts);
        $this->assertSame(UserOtpStatus::CANCELLED, $otp->status);
        $this->assertNotNull($otp->cancelled_at);

        $this->assertFalse(
            $service->verify($user, '123456', UserOtpType::LOGIN)
        );
    }
}

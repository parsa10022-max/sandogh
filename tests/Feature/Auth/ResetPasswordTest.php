<?php

namespace Tests\Feature\Auth;

use App\Enums\UserOtpStatus;
use App\Enums\UserOtpType;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_recovery_session_is_rejected(): void
    {
        $user = User::factory()->create();

        $otp = $this->createVerifiedOtp($user);

        $response = $this
            ->withSession([
                'forgot_password_user_id' => $user->id,
                'forgot_password_otp_id' => $otp->id,
                'forgot_password_verified' => true,
                'forgot_password_verified_at' => now()
                    ->subMinutes(11)
                    ->timestamp,
            ])
            ->get(route('password.reset'));

        $response->assertRedirect(route('password.request'));
    }

    public function test_password_can_be_reset_with_valid_verified_otp(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        $otp = $this->createVerifiedOtp($user);
        $verifiedAt = $otp->verified_at->timestamp;

        $response = $this
            ->withSession([
                'forgot_password_user_id' => $user->id,
                'forgot_password_otp_id' => $otp->id,
                'forgot_password_verified' => true,
                'forgot_password_verified_at' => $verifiedAt,
            ])
            ->put(route('password.reset.update'), [
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        $response->assertRedirect(route('login'));

        $this->assertTrue(
            Hash::check(
                'NewPassword123',
                $user->fresh()->password
            )
        );

        $this->assertSame(
            UserOtpStatus::CANCELLED,
            $otp->fresh()->status
        );
    }

    public function test_recovery_permission_cannot_be_reused_after_password_reset(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        $otp = $this->createVerifiedOtp($user);
        $verifiedAt = $otp->verified_at->timestamp;

        $session = [
            'forgot_password_user_id' => $user->id,
            'forgot_password_otp_id' => $otp->id,
            'forgot_password_verified' => true,
            'forgot_password_verified_at' => $verifiedAt,
        ];

        $this->withSession($session)
            ->put(route('password.reset.update'), [
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ])
            ->assertRedirect(route('login'));

        $this->put(route('password.reset.update'), [
            'password' => 'AnotherPassword123',
            'password_confirmation' => 'AnotherPassword123',
        ])->assertRedirect(route('password.request'));

        $this->assertTrue(
            Hash::check(
                'NewPassword123',
                $user->fresh()->password
            )
        );
    }

    private function createVerifiedOtp(User $user): UserOtp
    {
        return UserOtp::query()->create([
            'user_id' => $user->id,
            'mobile' => $user->mobile,
            'code' => Hash::make('123456'),
            'type' => UserOtpType::PASSWORD_RESET,
            'status' => UserOtpStatus::VERIFIED,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(2),
            'verified_at' => now(),
        ]);
    }
}

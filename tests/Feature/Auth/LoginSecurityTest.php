<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Enums\UserOtpType;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_start_login(): void
    {
        $user = User::factory()->inactive()->create();

        $response = $this->post(route('login.store'), [
            'username' => $user->username,
            'password' => '123456',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('user_otps', [
            'user_id' => $user->id,
            'type' => UserOtpType::LOGIN->value,
        ]);
        $this->assertGuest();
    }

    public function test_blocked_user_cannot_start_login(): void
    {
        $user = User::factory()->blocked()->create();

        $response = $this->post(route('login.store'), [
            'username' => $user->username,
            'password' => '123456',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('user_otps', [
            'user_id' => $user->id,
            'type' => UserOtpType::LOGIN->value,
        ]);
        $this->assertGuest();
    }

    public function test_active_user_can_start_login(): void
    {
        $user = User::factory()->active()->create();

        $response = $this->post(route('login.store'), [
            'username' => $user->username,
            'password' => '123456',
        ]);

        $response->assertRedirect(route('otp.form'));

        $this->assertDatabaseHas('user_otps', [
            'user_id' => $user->id,
            'type' => UserOtpType::LOGIN->value,
        ]);
    }

    public function test_blocked_user_cannot_verify_login_otp(): void
    {
        $user = User::factory()->active()->create();

        $this->withSession([
            'login_user_id' => $user->id,
        ]);

        UserOtp::create([
            'user_id' => $user->id,
            'mobile' => $user->mobile,
            'code' => Hash::make('123456'),
            'type' => UserOtpType::LOGIN,
            'status' => \App\Enums\UserOtpStatus::PENDING,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(2),
        ]);

        $user->update([
            'status' => UserStatus::BLOCKED,
        ]);

        $response = $this->post(route('otp.verify'), [
            'code' => '123456',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Enums\UserOtpType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_mobile_is_redirected_to_otp_form(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('password.otp.send'), [
            'mobile' => $user->mobile,
        ]);

        $response->assertRedirect(route('password.otp.form'));
        $response->assertSessionHas(
            'success',
            'اگر شماره موبایل در سامانه ثبت شده باشد، مراحل بازیابی رمز ادامه پیدا می‌کند.'
        );

        $this->assertDatabaseHas('user_otps', [
            'user_id' => $user->id,
            'type' => UserOtpType::PASSWORD_RESET->value,
        ]);

        $response = $this->get(route('password.otp.form'));

        $response->assertOk();
    }

    public function test_unknown_mobile_gets_same_initial_response_without_creating_otp(): void
    {
        $response = $this->post(route('password.otp.send'), [
            'mobile' => '09120000000',
        ]);

        $response->assertRedirect(route('password.otp.form'));
        $response->assertSessionHas(
            'success',
            'اگر شماره موبایل در سامانه ثبت شده باشد، مراحل بازیابی رمز ادامه پیدا می‌کند.'
        );

        $this->assertDatabaseCount('user_otps', 0);

        $response = $this->get(route('password.otp.form'));

        $response->assertOk();
    }

    public function test_unknown_mobile_cannot_verify_otp_or_reset_password(): void
    {
        $response = $this->post(route('password.otp.send'), [
            'mobile' => '09120000000',
        ]);

        $response->assertRedirect(route('password.otp.form'));

        $response = $this->post(route('password.otp.verify'), [
            'code' => '123456',
        ]);

        $response->assertSessionHasErrors('code');

        $response = $this->get(route('password.reset'));

        $response->assertRedirect(route('password.request'));
    }

    public function test_otp_form_requires_recovery_session(): void
    {
        $response = $this->get(route('password.otp.form'));

        $response->assertRedirect(route('password.request'));
    }
}

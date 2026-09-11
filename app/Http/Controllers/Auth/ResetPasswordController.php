<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserOtpType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /**
     * نمایش فرم تعیین رمز جدید
     */
    public function create()
    {
        if (! session('forgot_password_verified')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    /**
     * ذخیره رمز جدید
     */
    public function update(Request $request)
    {
        if (! session('forgot_password_verified')) {
            return redirect()->route('password.request');
        }

        $validated = $request->validate(
            [
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ],
            [
                'password.required' =>
                    'وارد کردن رمز عبور جدید الزامی است.',

                'password.string' =>
                    'رمز عبور جدید باید به صورت متن وارد شود.',

                'password.min' =>
                    'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.',

                'password.confirmed' =>
                    'تکرار رمز عبور جدید با رمز وارد شده مطابقت ندارد.',
            ]
        );

        $userId = session('forgot_password_user_id');

        $user = \App\Models\User::find($userId);

        if (! $user) {
            session()->forget([
                'forgot_password_user_id',
                'forgot_password_verified',
            ]);

            return redirect()->route('password.request');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // لغو OTPهای بازیابی باقی‌مانده
        app(\App\Services\OtpService::class)->cancel(
            $user,
            UserOtpType::PASSWORD_RESET
        );

        // پایان کامل فرآیند بازیابی
        session()->forget([
            'forgot_password_user_id',
            'forgot_password_verified',
        ]);

        return redirect()
            ->route('login')
            ->with(
                'success',
                'رمز عبور با موفقیت تغییر کرد. اکنون می‌توانید وارد شوید.'
            );
    }
}

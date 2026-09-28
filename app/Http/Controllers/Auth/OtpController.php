<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    public function __construct(
        private OtpService $otpService
    ) {
    }

    /**
     * نمایش فرم ورود کد OTP
     */
    public function showVerifyForm()
    {
        $user = $this->getLoginUser();

        if (! $user) {
            return redirect()
                ->route('login');
        }

        $otp = null;

        if (config('app.debug')) {
            $otp = $this->otpService->getLastPendingOtp($user);
        }

        return view(
            'auth.otp',
            compact('otp')
        );
    }

    /**
     * بررسی کد OTP
     */
    public function verify(OtpRequest $request)
    {
        $user = $this->getLoginUser();

        if (! $user) {
            return redirect()
                ->route('login');
        }

        $data = $request->validated();

        if (! $this->otpService->verify(
            $user,
            $data['code']
        )) {
            return back()
                ->withErrors([
                    'code' =>
                        'کد تأیید صحیح نیست یا منقضی شده است.',
                ]);
        }

        Auth::login($user);

        $user->update([
            'last_login_at' => now(),
        ]);

        $request->session()->forget(
            'login_user_id'
        );

        $request->session()->regenerate();

        if ($user->role === UserRole::CUSTOMER) {
            return redirect()->intended(
                route('customer.dashboard')
            );
        }

        return redirect()
            ->route('dashboard');
    }

    private function getLoginUser(): ?User
    {
        $userId = session('login_user_id');

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }
}

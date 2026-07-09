<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\OtpRequest;

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
            return redirect()->route('login');
        }

        $otp = null;

        if (config('app.debug')) {
            $otp = $this->otpService->getLastPendingOtp($user);
        }

        return view('auth.otp', compact('otp'));
    }
    /**
     * بررسی کد OTP
     */
    public function verify(OtpRequest $request)
    {
        $user = $this->getLoginUser();

        if (! $user) {
            return redirect()->route('login');
        }

        $data = $request->validated();

        if (! $this->otpService->verify($user, $data['code'])) {
            return back()->withErrors([
                'code' => 'کد تأیید صحیح نیست یا منقضی شده است.',
            ]);
        }

        Auth::login($user);

        session()->forget('login_user_id');

        return redirect()->route('dashboard');
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

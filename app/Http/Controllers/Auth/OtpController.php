<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserOtpType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
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
            return redirect()->route('login');
        }

        if ($this->isCustomerMembershipExpired($user)) {
            return $this->expiredMembershipRedirect();
        }

        $testOtp = null;

        if (config('app.debug')) {
            $otp = $this->otpService->getLastPendingOtp(
                $user,
                UserOtpType::LOGIN
            );

            if ($otp) {
                $testOtp = session('test_otp');
            }
        }

        return view(
            'auth.otp',
            compact('testOtp')
        );
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

        if ($this->isCustomerMembershipExpired($user)) {
            return $this->expiredMembershipRedirect();
        }

        if ($user->status !== UserStatus::ACTIVE) {
            $request->session()->forget('login_user_id');

            return redirect()
                ->route('login')
                ->withErrors([
                    'username' => 'حساب کاربری شما غیرفعال یا مسدود است.',
                ]);
        }

        $data = $request->validated();

        if (! $this->otpService->verify(
            $user,
            $data['code']
        )) {
            return back()
                ->withErrors([
                    'code' => 'کد تأیید صحیح نیست یا منقضی شده است.',
                ]);
        }

        Auth::login($user);

        $user->update([
            'last_login_at' => now(),
        ]);

        $request->session()->forget('login_user_id');

        $request->session()->regenerate();

        if ($user->role === UserRole::CUSTOMER) {
            return redirect()->intended(
                route('customer.dashboard')
            );
        }

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

    private function isCustomerMembershipExpired(User $user): bool
    {
        if ($user->role !== UserRole::CUSTOMER) {
            return false;
        }

        return ! $user->customer_id
            || ! $user->customer
            || ! $user->created_at
            || now()->greaterThanOrEqualTo(
                $user->created_at->copy()->addYears(2)
            );
    }

    private function expiredMembershipRedirect()
    {
        session()->forget('login_user_id');

        return redirect()
            ->route('login')
            ->withErrors([
                'username' => '.مدت عضویت اینترنتی شما به پایان رسیده است. برای تمدید دسترسی، لطفاً با صندوق قرض‌الحسنه به شماره ۰۷۱-۳۲۶۱۳۲۱۷ تماس بگیرید.',
            ]);
    }
}

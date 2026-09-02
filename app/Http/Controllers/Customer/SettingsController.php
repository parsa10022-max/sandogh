<?php

namespace App\Http\Controllers\Customer;

use App\Enums\UserOtpType;
use App\Http\Controllers\Controller;
use App\Models\UserOtp;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        return view(
            'customer.settings.index',
            compact('user')
        );
    }

    /**
     * ویرایش اطلاعات حساب کاربری
     */
    public function updateAccount(
        Request $request,
        OtpService $otpService
    ): RedirectResponse {
        $user = Auth::user();

        $customerId = $user->customer_id;

        $validated = $request->validateWithBag('account', [
            'username' => [
                'required',
                'string',
                'min:4',
                'max:100',
                Rule::unique('users', 'username')
                    ->ignore($user->id),
            ],

            'mobile' => [
                'required',
                'digits:11',

                Rule::unique('users', 'mobile')
                    ->ignore($user->id),

                Rule::unique('customers', 'mobile')
                    ->when(
                        $customerId !== null,
                        fn ($rule) => $rule->ignore($customerId)
                    ),
            ],
        ], [
            'username.required' =>
                'نام کاربری را وارد کنید.',
            'username.min' =>
                'نام کاربری باید حداقل ۴ کاراکتر باشد.',
            'username.max' =>
                'نام کاربری نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',
            'username.unique' =>
                'این نام کاربری قبلاً استفاده شده است.',
            'mobile.required' =>
                'شماره موبایل را وارد کنید.',
            'mobile.digits' =>
                'شماره موبایل باید دقیقاً ۱۱ رقم باشد.',
            'mobile.unique' =>
                'این شماره موبایل قبلاً استفاده شده است.',
        ]);

        if ($validated['mobile'] === $user->mobile) {
            $user->update([
                'username' => $validated['username'],
            ]);

            return back()->with(
                'account_success',
                'اطلاعات حساب کاربری با موفقیت به‌روزرسانی شد.'
            );
        }

        $otp = $otpService->generate(
            $user,
            UserOtpType::CHANGE_MOBILE,
            $validated['mobile'],
            $request
        );

        session([
            'pending_account_update' => [
                'username' => $validated['username'],
                'mobile' => $validated['mobile'],
                'otp_id' => $otp->id,
            ],
        ]);

        return redirect()
            ->route('customer.settings.mobile.verify')
            ->with(
                'account_otp_success',
                'کد تأیید به شماره موبایل جدید ارسال شد.'
            );
    }

    /**
     * نمایش صفحه تأیید تغییر شماره موبایل
     */
    public function showMobileVerification(): View|RedirectResponse
    {
        $user = Auth::user();

        $pending = session('pending_account_update');

        if (! $pending) {
            return redirect()
                ->route('customer.settings.index')
                ->with(
                    'account_error',
                    'درخواستی برای تغییر شماره موبایل وجود ندارد.'
                );
        }

        $otp = UserOtp::query()
            ->where('id', $pending['otp_id'])
            ->where('user_id', $user->id)
            ->where('type', UserOtpType::CHANGE_MOBILE)
            ->valid()
            ->first();

        if (! $otp) {
            session()->forget('pending_account_update');

            return redirect()
                ->route('customer.settings.index')
                ->with(
                    'account_error',
                    'کد تأیید منقضی شده است. دوباره درخواست کنید.'
                );
        }

        return view(
            'customer.settings.verify-mobile',
            compact('user', 'pending', 'otp')
        );
    }

    /**
     * تأیید تغییر شماره موبایل
     */
    public function verifyMobile(
        Request $request,
        OtpService $otpService
    ): RedirectResponse {
        $user = Auth::user();

        $pending = session('pending_account_update');

        if (! $pending) {
            return redirect()
                ->route('customer.settings.index')
                ->with(
                    'account_error',
                    'درخواستی برای تغییر شماره موبایل وجود ندارد.'
                );
        }

        $request->validateWithBag('otp', [
            'code' => [
                'required',
                'digits:6',
            ],
        ], [
            'code.required' =>
                'کد تأیید را وارد کنید.',
            'code.digits' =>
                'کد تأیید باید ۶ رقم باشد.',
        ]);

        $otp = UserOtp::query()
            ->where('id', $pending['otp_id'])
            ->where('user_id', $user->id)
            ->where('type', UserOtpType::CHANGE_MOBILE)
            ->valid()
            ->first();

        if (! $otp) {
            session()->forget('pending_account_update');

            return redirect()
                ->route('customer.settings.index')
                ->with(
                    'account_error',
                    'کد تأیید منقضی شده است. دوباره درخواست کنید.'
                );
        }

        if (! $otpService->verify(
            $user,
            $request->input('code'),
            UserOtpType::CHANGE_MOBILE
        )) {
            return back()
                ->withErrors([
                    'code' => 'کد تأیید صحیح نیست.',
                ], 'otp')
                ->withInput();
        }

        DB::transaction(function () use ($user, $pending) {
            $user->update([
                'username' => $pending['username'],
                'mobile' => $pending['mobile'],
            ]);

            if ($user->customer) {
                $user->customer->update([
                    'mobile' => $pending['mobile'],
                ]);
            }
        });

        session()->forget('pending_account_update');

        return redirect()
            ->route('customer.settings.index')
            ->with(
                'account_success',
                'شماره موبایل و اطلاعات حساب با موفقیت به‌روزرسانی شد.'
            );
    }

    /**
     * درخواست تغییر رمز عبور
     */
    public function updatePassword(
        Request $request,
        OtpService $otpService
    ): RedirectResponse {
        $user = Auth::user();

        $validated = $request->validateWithBag('password', [
            'current_password' => [
                'required',
                'current_password',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'current_password.required' =>
                'رمز عبور فعلی را وارد کنید.',

            'current_password.current_password' =>
                'رمز عبور فعلی صحیح نیست.',

            'password.required' =>
                'رمز عبور جدید را وارد کنید.',

            'password.min' =>
                'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.',

            'password.confirmed' =>
                'تکرار رمز عبور با رمز جدید مطابقت ندارد.',
        ]);

        $otp = $otpService->generate(
            $user,
            UserOtpType::CHANGE_PASSWORD,
            $user->mobile,
            $request
        );

        session([
            'pending_password_update' => [
                'password' => $validated['password'],
                'otp_id' => $otp->id,
            ],
        ]);

        return redirect()
            ->route('customer.settings.password.verify')
            ->with(
                'password_otp_success',
                'کد تأیید به شماره موبایل شما ارسال شد.'
            );
    }

    /**
     * نمایش صفحه تأیید تغییر رمز عبور
     */
    public function showPasswordVerification(): View|RedirectResponse
    {
        $user = Auth::user();

        $pending = session('pending_password_update');

        if (! $pending) {
            return redirect()
                ->route('customer.settings.index')
                ->with(
                    'password_error',
                    'درخواستی برای تغییر رمز عبور وجود ندارد.'
                );
        }

        $otp = UserOtp::query()
            ->where('id', $pending['otp_id'])
            ->where('user_id', $user->id)
            ->where('type', UserOtpType::CHANGE_PASSWORD)
            ->valid()
            ->first();

        if (! $otp) {
            session()->forget('pending_password_update');

            return redirect()
                ->route('customer.settings.index')
                ->with(
                    'password_error',
                    'کد تأیید منقضی شده است. دوباره درخواست کنید.'
                );
        }

        return view(
            'customer.settings.verify-password',
            compact('user', 'pending', 'otp')
        );
    }

    /**
     * تأیید OTP و تغییر رمز عبور
     */
    public function verifyPassword(
        Request $request,
        OtpService $otpService
    ): RedirectResponse {
        $user = Auth::user();

        $pending = session('pending_password_update');

        if (! $pending) {
            return redirect()
                ->route('customer.settings.index')
                ->with(
                    'password_error',
                    'درخواستی برای تغییر رمز عبور وجود ندارد.'
                );
        }

        $request->validateWithBag('otp', [
            'code' => [
                'required',
                'digits:6',
            ],
        ], [
            'code.required' =>
                'کد تأیید را وارد کنید.',

            'code.digits' =>
                'کد تأیید باید ۶ رقم باشد.',
        ]);

        $otp = UserOtp::query()
            ->where('id', $pending['otp_id'])
            ->where('user_id', $user->id)
            ->where('type', UserOtpType::CHANGE_PASSWORD)
            ->valid()
            ->first();

        if (! $otp) {
            session()->forget('pending_password_update');

            return redirect()
                ->route('customer.settings.index')
                ->with(
                    'password_error',
                    'کد تأیید منقضی شده است. دوباره درخواست کنید.'
                );
        }

        if (! $otpService->verify(
            $user,
            $request->input('code'),
            UserOtpType::CHANGE_PASSWORD
        )) {
            return back()
                ->withErrors([
                    'code' => 'کد تأیید صحیح نیست.',
                ], 'otp')
                ->withInput();
        }

        $user->update([
            'password' => Hash::make(
                $pending['password']
            ),
        ]);

        session()->forget('pending_password_update');

        return redirect()
            ->route('customer.settings.index')
            ->with(
                'success',
                'رمز عبور با موفقیت تغییر کرد.'
            );
    }
}

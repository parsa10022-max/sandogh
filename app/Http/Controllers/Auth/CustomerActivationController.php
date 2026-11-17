<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerActivation\CreateAccountRequest;
use App\Http\Requests\Auth\OtpRequest;
use App\Http\Requests\CustomerActivation\SendOtpRequest;
use App\Models\Customer;
use App\Services\CustomerAccountActivationService;
use App\Services\CustomerActivationOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class CustomerActivationController extends Controller
{
    public function __construct(
        private readonly CustomerActivationOtpService $otpService,
        private readonly CustomerAccountActivationService $accountService
    ) {
    }

    public function create(): View
    {
        return view('auth.customer-activation');
    }


    public function sendOtp(
        SendOtpRequest $request
    ): RedirectResponse {
        $mobile = $request->validated('mobile');

        $customer = Customer::query()
            ->active()
            ->where('mobile', $mobile)
            ->first();

        if (! $customer) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'عضوی با این شماره موبایل در صندوق پیدا نشد.'
                );
        }

        if (! $customer->online_activation_approved) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'فعال‌سازی اینترنتی این عضو هنوز توسط صندوق تأیید نشده است.'
                );
        }

        if ($customer->user()->exists()) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'برای این عضو قبلاً حساب کاربری ایجاد شده است. لطفاً وارد سامانه شوید.'
                );
        }

        // پاک‌سازی اطلاعات OTP قبلی قبل از تولید کد جدید
        session()->forget([
            'customer_activation_verified',
            'customer_activation_test_otp',
        ]);

        // تولید OTP جدید
        $this->otpService->generate(
            $customer,
            $request
        );

        session([
            'customer_activation_id' => $customer->id,
            'customer_activation_mobile' => $customer->mobile,
        ]);

        return redirect()
            ->route('customer-activation.otp')
            ->with(
                'success',
                'کد تأیید برای شماره موبایل شما ایجاد شد.'
            );
    }


    public function showOtp(): View|RedirectResponse
    {
        $customer = $this->approvedCustomerOrRedirect();

        if ($customer instanceof RedirectResponse) {
            return $customer;
        }

        $otp = $this->otpService->getLastPendingOtp($customer);

        if (! $otp) {
            return redirect()
                ->route('customer-activation.create')
                ->with(
                    'error',
                    'کد تأیید معتبر پیدا نشد. دوباره درخواست کد کنید.'
                );
        }

        return view('auth.customer-activation-otp', [
            'mobile' => $customer->mobile,
            'testOtp' => config('app.debug')
                ? session('customer_activation_test_otp')
                : null,
        ]);
    }

    public function verifyOtp(
        OtpRequest $request
    ): RedirectResponse {
        $customer = $this->approvedCustomerOrRedirect();

        if ($customer instanceof RedirectResponse) {
            return $customer;
        }

        if ($customer->user()->exists()) {
            $this->clearActivationSession();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'برای این عضو قبلاً حساب کاربری ایجاد شده است.'
                );
        }

        $verified = $this->otpService->verify(
            $customer,
            $request->validated('code')
        );

        if (! $verified) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'کد تأیید نادرست یا منقضی شده است.'
                );
        }

        session([
            'customer_activation_verified' => true,
        ]);

        session()->forget(
            'customer_activation_test_otp'
        );

        return redirect()
            ->route('customer-activation.account')
            ->with(
                'success',
                'شماره موبایل با موفقیت تأیید شد.'
            );
    }

    public function showAccount(): View|RedirectResponse
    {
        if (! session('customer_activation_verified')) {
            return redirect()
                ->route('customer-activation.create')
                ->with(
                    'error',
                    'ابتدا شماره موبایل خود را تأیید کنید.'
                );
        }

        $customer = $this->approvedCustomerOrRedirect();

        if ($customer instanceof RedirectResponse) {
            return $customer;
        }

        if ($customer->user()->exists()) {
            $this->clearActivationSession();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'برای این عضو قبلاً حساب کاربری ایجاد شده است.'
                );
        }

        return view(
            'auth.customer-activation-account',
            compact('customer')
        );
    }

    public function createAccount(
        CreateAccountRequest $request
    ): RedirectResponse {
        if (! session('customer_activation_verified')) {
            return redirect()
                ->route('customer-activation.create')
                ->with(
                    'error',
                    'ابتدا شماره موبایل خود را تأیید کنید.'
                );
        }

        $customer = $this->approvedCustomerOrRedirect();

        if ($customer instanceof RedirectResponse) {
            return $customer;
        }

        if ($customer->user()->exists()) {
            $this->clearActivationSession();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'برای این عضو قبلاً حساب کاربری ایجاد شده است.'
                );
        }

        try {
            $user = $this->accountService->create(
                $customer,
                $request->validated()
            );
        } catch (RuntimeException $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'ایجاد حساب کاربری انجام نشد. لطفاً دوباره تلاش کنید.'
                );
        }

        $this->clearActivationSession();

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('customer.dashboard')
            ->with(
                'success',
                'حساب کاربری شما با موفقیت ایجاد شد.'
            );
    }

    /**
     * دریافت مشتری فقط در صورت داشتن مجوز فعال‌سازی اینترنتی
     */
    private function approvedCustomerOrRedirect(): Customer|RedirectResponse
    {
        $customerId = session('customer_activation_id');

        if (! $customerId) {
            $this->clearActivationSession();

            return redirect()
                ->route('customer-activation.create')
                ->with(
                    'error',
                    'جلسه فعال‌سازی منقضی شده است. دوباره شروع کنید.'
                );
        }

        $customer = Customer::query()
            ->active()
            ->find($customerId);

        if (! $customer) {
            $this->clearActivationSession();

            return redirect()
                ->route('customer-activation.create')
                ->with(
                    'error',
                    'عضو موردنظر پیدا نشد یا وضعیت عضویت او فعال نیست.'
                );
        }

        if (! $customer->online_activation_approved) {
            $this->clearActivationSession();

            return redirect()
                ->route('customer-activation.create')
                ->with(
                    'error',
                    'مجوز فعال‌سازی اینترنتی این عضو توسط صندوق تأیید نشده یا لغو شده است.'
                );
        }

        return $customer;
    }

    /**
     * پاک‌سازی کامل اطلاعات نشست فعال‌سازی
     */
    private function clearActivationSession(): void
    {
        session()->forget([
            'customer_activation_id',
            'customer_activation_mobile',
            'customer_activation_verified',
            'customer_activation_test_otp',
        ]);
    }
}

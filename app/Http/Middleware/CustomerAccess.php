<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerAccess
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->status !== UserStatus::ACTIVE) {
            return $this->logoutAndRedirect(
                $request,
                'حساب کاربری شما غیرفعال یا مسدود است.'
            );
        }

        if (! $user->customer_id) {
            abort(403);
        }

        // کنترل پایان عضویت اینترنتی فقط برای مشتریان
        if ($user->role === UserRole::CUSTOMER) {
            if (
                ! $user->customer ||
                ! $user->created_at ||
                now()->greaterThanOrEqualTo(
                    $user->created_at->copy()->addYears(2)
                )
            ) {
                return $this->logoutAndRedirect(
                    $request,
                    'مدت عضویت اینترنتی شما به پایان رسیده است. برای تمدید دسترسی، لطفاً با صندوق قرض‌الحسنه به شماره ۰۷۱-۳۲۶۱۳۲۱۷ تماس بگیرید.'
                );
            }
        }

        return $next($request);
    }

    private function logoutAndRedirect(
        Request $request,
        string $message
    ): Response {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors([
                'username' => $message,
            ]);
    }
}

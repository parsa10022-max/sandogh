<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role === UserRole::CUSTOMER) {
            abort(403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('staff')->check() && ! Auth::guard('staff')->user()->is_active) {
            Auth::guard('staff')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('staff.login')->withErrors([
                'email' => __('staff.inactive_account'),
            ]);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Staff pages need a signed-in, active staff member. A deactivated account counts as not signed in, and a person with
 * a temporary password may only use the "choose your password" page until they have set their own.
 */
class EnsureStaff
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user || ! $user->active) {
            if ($user) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect('/staff/login');
        }
        if ($user->must_change_password && ! $request->is('staff/password*', 'staff/logout')) {
            return redirect('/staff/password');
        }

        return $next($request);
    }
}

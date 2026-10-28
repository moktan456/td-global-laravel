<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** An admin still on a temporary password must choose their own (on the staff page) before using the admin panel. */
class EnsureAdminReady
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->must_change_password) {
            return redirect('/staff/password');
        }

        return $next($request);
    }
}

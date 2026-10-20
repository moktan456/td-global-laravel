<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Super admin only. Everyone else goes back to the shipments list. */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()?->isAdmin()) {
            return redirect('/staff');
        }

        return $next($request);
    }
}

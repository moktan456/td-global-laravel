<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser-side protections on every page: nobody else can frame the site (clickjacking on the staff sign-in), the browser
 * must trust the declared content types, links leak only the site's address, and powerful device features are off.
 * HSTS (HTTPS only, for a year) is opt-in with TD_HSTS=true, to be switched on once the real domain serves HTTPS.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        // Not a full script policy (the pages use inline scripts): it blocks plug-ins, base-tag tricks, framing and
        // form posts to other sites.
        $response->headers->set('Content-Security-Policy', "base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'");
        if (config('td.hsts') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}

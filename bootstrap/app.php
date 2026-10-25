<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureStaff;
use App\Http\Middleware\JsxWhitespace;
use App\Http\Middleware\SecurityHeaders;
use App\Support\FormState;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [JsxWhitespace::class, SecurityHeaders::class]);
        $middleware->alias(['staff' => EnsureStaff::class, 'admin' => EnsureAdmin::class]);
        // Telegram calls the webhook; it proves itself with a secret header instead of a CSRF token.
        $middleware->validateCsrfTokens(except: ['api/telegram/webhook']);
        $middleware->redirectGuestsTo('/staff/login');
        // Behind a host's proxy or CDN the visitor's real IP is what the rate limits should count.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Inside the staff workspace, errors look like the workspace, not the public website.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->is('staff', 'staff/*') || $request->expectsJson()) {
                return null;
            }
            if ($e->getStatusCode() === 404 && auth()->check()) {
                return response()->view('errors.staff-404', [], 404);
            }
            if ($e->getStatusCode() === 419) {   // the page sat open until the session ended
                session()->flash('forms.login', FormState::failed('Your session ended. Please sign in again.')->toArray());

                return redirect('/staff/login');
            }

            return null;
        });
    })->create();

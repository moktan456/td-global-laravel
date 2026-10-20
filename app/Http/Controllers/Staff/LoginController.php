<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\FormState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

/** Staff sign in with a username and password (or an email, for accounts set up with a real address). */
class LoginController extends Controller
{
    private const WRONG = "That username or password isn't right. Check both and try again.";

    public function show()
    {
        if (Auth::check() && Auth::user()->active) {
            return redirect('/staff');
        }

        return view('staff.login', ['state' => form_state('login')]);
    }

    /** One message for every failure, so it can't be used to find usernames. */
    public function login(Request $request)
    {
        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', ''); // never trimmed or sent back to the page
        $values = ['username' => $username];

        $errors = [];
        if ($username === '') {
            $errors['username'] = 'Enter your username.';
        }
        if ($password === '') {
            $errors['password'] = 'Enter your password.';
        }
        if ($errors) {
            return $this->formResponse($request, 'login', 'staff.login-form', new FormState('error', 'Please check the highlighted fields.', $errors, $values));
        }

        $key = 'staff-login:'.mb_strtolower($username).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $wait = RateLimiter::availableIn($key);

            return $this->formResponse($request, 'login', 'staff.login-form', FormState::failed("Too many tries. Please wait {$wait} seconds and try again.", $values));
        }

        $field = str_contains($username, '@') ? 'email' : 'username';
        $credentials = [$field => mb_strtolower($username), 'password' => $password, 'active' => true];
        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($key, 60);

            return $this->formResponse($request, 'login', 'staff.login-form', FormState::failed(self::WRONG, $values));
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();

        return $this->formRedirect($request, Auth::user()->must_change_password ? '/staff/password' : '/staff');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/staff/login');
    }
}

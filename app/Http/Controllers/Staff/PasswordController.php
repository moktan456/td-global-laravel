<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\FormState;
use App\Support\StaffAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/** A signed-in staff member chooses their own password. Used on first sign-in and any time after. */
class PasswordController extends Controller
{
    public function show()
    {
        return view('staff.password', ['state' => form_state('password'), 'user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $password = (string) $request->input('password', '');
        $confirm = (string) $request->input('confirm', '');
        $errors = [];
        StaffAccounts::checkNewPassword($password, $errors, 'password', $user->username);
        if (! isset($errors['password']) && $password !== $confirm) {
            $errors['confirm'] = "The two passwords don't match.";
        }
        if (! isset($errors['password']) && Hash::check($password, $user->password)) {
            $errors['password'] = "Choose a password you haven't used before.";
        }
        if ($errors) {
            $msg = count($errors) === 1 ? 'There is 1 problem.' : 'There are some problems.';

            return $this->formResponse($request, 'password', 'staff.password-form', new FormState('error', isset($errors['password']) && str_contains($errors['password'], "haven't used") ? "Choose a password you haven't used before." : $msg, $errors));
        }
        $user->forceFill(['password' => $password, 'must_change_password' => false])->save();

        return $this->formRedirect($request, '/staff');
    }
}

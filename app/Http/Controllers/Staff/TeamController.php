<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\FormState;
use App\Support\StaffAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Super admins add staff with a username and temporary password, reset passwords, and switch people off. */
class TeamController extends Controller
{
    public function index()
    {
        return view('staff.team', ['team' => User::orderBy('created_at')->orderBy('id')->get(), 'state' => form_state('team')]);
    }

    public function store(Request $request)
    {
        $values = collect(['full_name', 'username', 'password', 'role'])->mapWithKeys(fn ($k) => [$k => trim((string) $request->input($k, ''))])->all();
        $values['username'] = mb_strtolower($values['username']);
        $safe = ['password' => ''] + $values;
        $errors = [];
        if ($values['full_name'] === '') {
            $errors['full_name'] = "Enter the person's name.";
        }
        StaffAccounts::checkUsername($values['username'], $errors);
        StaffAccounts::checkNewPassword($values['password'], $errors, 'password', $values['username']);
        if (! in_array($values['role'], ['staff', 'admin'], true)) {
            $errors['role'] = 'Choose a role.';
        }
        if ($errors) {
            return $this->formResponse($request, 'team', 'staff.team-form', FormState::invalid($errors, $safe));
        }
        if (User::where('username', $values['username'])->exists()) {
            return $this->formResponse($request, 'team', 'staff.team-form', FormState::invalid(['username' => 'That username is taken.'], $safe));
        }

        User::create([
            'name' => $values['full_name'], 'username' => $values['username'], 'email' => StaffAccounts::usernameToEmail($values['username']),
            'password' => $values['password'], 'role' => $values['role'], 'active' => true, 'must_change_password' => true,
        ]);

        return $this->formResponse($request, 'team', 'staff.team-form', FormState::success(
            "{$values['full_name']} can now sign in as \"{$values['username']}\" with the temporary password. They'll choose their own at first sign-in."
        ));
    }

    /** Give a staff member a new temporary password (forgotten password, or after a shared device). */
    public function resetPassword(Request $request, User $user)
    {
        $password = (string) $request->input('password', '');
        $errors = [];
        StaffAccounts::checkNewPassword($password, $errors, 'password', $user->username);
        if ($errors) {
            return $this->formResponse($request, "staffrow{$user->id}", 'staff.team-row', FormState::invalid($errors, []), ['member' => $user]);
        }
        $user->forceFill(['password' => $password, 'must_change_password' => true])->save();
        DB::table('sessions')->where('user_id', $user->id)->delete();   // sign them out everywhere so the old password stops working at once

        return $this->formResponse($request, "staffrow{$user->id}", 'staff.team-row', FormState::success("New temporary password set for {$user->name}. They'll choose their own at next sign-in."), ['member' => $user]);
    }

    /** Switch a staff member off (they can no longer sign in or use the dashboard) or back on. */
    public function setActive(Request $request, User $user)
    {
        $makeActive = $request->input('active') === 'true';
        $fail = fn (string $m) => $this->formResponse($request, "staffrow{$user->id}", 'staff.team-row', FormState::failed($m), ['member' => $user]);
        if ($user->id === $request->user()->id) {
            return $fail("You can't switch off your own account.");
        }
        if (! $makeActive && $user->role === 'admin' && User::where('role', 'admin')->where('active', true)->count() <= 1) {
            return $fail('Keep at least one active super admin.');
        }
        $user->forceFill(['active' => $makeActive])->save();
        if (! $makeActive) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return $this->formResponse($request, "staffrow{$user->id}", 'staff.team-row', FormState::success("{$user->name} is ".($makeActive ? 'active again' : 'switched off').'.'), ['member' => $user->fresh()]);
    }
}

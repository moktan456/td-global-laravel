<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\StaffAccounts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Last-resort recovery for a forgotten password, run on the server (so it needs SSH access to the hosting account):
 *   php artisan staff:reset-password nima
 * The new password is asked for (hidden) and is temporary: the person chooses their own at next sign-in, and they are
 * signed out everywhere. Normally another super admin does this from Staff → Team instead.
 */
class ResetStaffPassword extends Command
{
    protected $signature = 'staff:reset-password {username} {--password= : Temporary password (asked for if left out)}';

    protected $description = 'Set a new temporary password for a staff member or super admin who forgot theirs';

    public function handle(): int
    {
        $username = strtolower($this->argument('username'));
        $user = User::where('username', $username)->first();
        if (! $user) {
            $this->error("There is no account with the username \"{$username}\". Existing usernames: ".User::orderBy('id')->pluck('username')->implode(', '));

            return self::FAILURE;
        }
        $password = $this->option('password') ?: $this->secret('New temporary password (at least '.StaffAccounts::MIN_PASSWORD_LENGTH.' characters)');
        $errors = [];
        StaffAccounts::checkNewPassword((string) $password, $errors, 'password', $username);
        if ($errors) {
            $this->error($errors['password']);

            return self::FAILURE;
        }
        $user->forceFill(['password' => $password, 'must_change_password' => true])->save();
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $this->info("New temporary password set for {$username}. They choose their own at the next sign-in.");
        if (! $user->active) {
            $this->warn("{$username} is switched off and still cannot sign in. A super admin can switch them back on under Staff → Team.");
        }

        return self::SUCCESS;
    }
}

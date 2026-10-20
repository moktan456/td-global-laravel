<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\StaffAccounts;
use Illuminate\Console\Command;

/**
 * Creates the first super admin (the web "Team" page needs one to exist), or any staff account from the server:
 *   php artisan staff:create nima "Nima Moktan" --admin
 * The password is asked for (hidden), or pass --password. The person must choose their own at first sign-in.
 */
class CreateStaff extends Command
{
    protected $signature = 'staff:create {username} {name} {--admin : Make a super admin} {--password= : Temporary password (asked for if left out)}';

    protected $description = 'Create a staff account (use --admin for a super admin)';

    public function handle(): int
    {
        $username = strtolower($this->argument('username'));
        $errors = [];
        StaffAccounts::checkUsername($username, $errors);
        if ($errors) {
            $this->error($errors['username']);

            return self::FAILURE;
        }
        if (User::where('username', $username)->exists()) {
            $this->error("The username \"{$username}\" is taken.");

            return self::FAILURE;
        }
        $password = $this->option('password') ?: $this->secret('Temporary password (at least '.StaffAccounts::MIN_PASSWORD_LENGTH.' characters)');
        StaffAccounts::checkNewPassword((string) $password, $errors, 'password', $username);
        if ($errors) {
            $this->error($errors['password']);

            return self::FAILURE;
        }
        User::create([
            'name' => $this->argument('name'), 'username' => $username, 'email' => StaffAccounts::usernameToEmail($username),
            'password' => $password, 'role' => $this->option('admin') ? 'admin' : 'staff', 'active' => true, 'must_change_password' => true,
        ]);
        $this->info("Created {$username} ".($this->option('admin') ? '(super admin)' : '(staff)').'. They choose their own password at first sign-in.');

        return self::SUCCESS;
    }
}

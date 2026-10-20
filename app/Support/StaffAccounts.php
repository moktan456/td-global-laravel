<?php

namespace App\Support;

/** Username and password rules for staff (ported from src/lib/portal/staff-accounts.ts). */
class StaffAccounts
{
    /** A username maps to a private address on a reserved ".invalid" domain: nothing is ever delivered there. */
    public const EMAIL_DOMAIN = 'staff.td-global.invalid';

    public const USERNAME_PATTERN = '/^[a-z0-9][a-z0-9._-]{2,29}$/';

    public const MIN_PASSWORD_LENGTH = 10;

    public static function usernameToEmail(string $username): string
    {
        return strtolower($username).'@'.self::EMAIL_DOMAIN;
    }

    public static function checkUsername(string $value, array &$errors, string $name = 'username'): void
    {
        if (! preg_match(self::USERNAME_PATTERN, $value)) {
            $errors[$name] = 'Use 3 to 30 lowercase letters, numbers, dots, dashes or underscores, starting with a letter or number.';
        }
    }

    public static function checkNewPassword(string $password, array &$errors, string $name = 'password', ?string $username = null): void
    {
        $min = self::MIN_PASSWORD_LENGTH;
        if (mb_strlen($password) < $min) {
            $errors[$name] = "Use at least {$min} characters.";
        } elseif (mb_strlen($password) > 72) {
            $errors[$name] = 'Use 72 characters or fewer.';
        } elseif ($username && str_contains(mb_strtolower($password), mb_strtolower($username))) {
            $errors[$name] = "The password can't contain the username.";
        } elseif (preg_match('/^(.)\1+$/us', $password)) {
            $errors[$name] = "Choose a password that isn't one repeated character.";
        }
    }
}

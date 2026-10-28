<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/** A staff member. `role = admin` is the super admin; `active = false` switches the account off. */
#[Fillable(['name', 'username', 'email', 'password', 'role', 'active', 'must_change_password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    /** The website admin panel (/admin) is for switched-on admins only; other staff use /staff. */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->active && $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}

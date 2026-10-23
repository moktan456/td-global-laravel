<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class ResetStaffPasswordCommandTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_a_forgotten_super_admin_password_can_be_reset_from_the_server(): void
    {
        $admin = $this->admin();
        DB::table('sessions')->insert(['id' => 'old-session', 'user_id' => $admin->id, 'payload' => '', 'last_activity' => now()->timestamp]);

        $this->artisan('staff:reset-password', ['username' => $admin->username])
            ->expectsQuestion('New temporary password (at least 10 characters)', 'a-new-temporary-pass')
            ->expectsOutputToContain('New temporary password set')
            ->assertSuccessful();

        $admin->refresh();
        $this->assertTrue(Hash::check('a-new-temporary-pass', $admin->password));
        $this->assertTrue($admin->must_change_password);
        $this->assertDatabaseMissing('sessions', ['id' => 'old-session']);
        $this->post('/staff/login', ['username' => $admin->username, 'password' => 'a-new-temporary-pass'])->assertRedirect();
    }

    public function test_it_refuses_an_unknown_username_and_a_weak_password(): void
    {
        $admin = $this->admin();
        $this->artisan('staff:reset-password', ['username' => 'nobody'])->expectsOutputToContain('no account')->assertFailed();
        $this->artisan('staff:reset-password', ['username' => $admin->username, '--password' => 'short'])->expectsOutputToContain('at least 10')->assertFailed();
        $this->assertFalse(Hash::check('short', User::find($admin->id)->password));
    }
}

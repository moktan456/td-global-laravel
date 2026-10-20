<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class StaffLoginTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_staff_pages_need_a_login(): void
    {
        foreach (['/staff', '/staff/clients', '/staff/shipments/new', '/staff/team', '/staff/telegram', '/staff/password'] as $path) {
            $this->get($path)->assertRedirect('/staff/login');
        }
    }

    public function test_signs_in_with_username_and_password(): void
    {
        $user = $this->staff(['username' => 'pema']);
        $this->post('/staff/login', ['username' => 'Pema', 'password' => 'a-long-password-1'])->assertRedirect('/staff');
        $this->assertAuthenticatedAs($user);
        $this->get('/staff')->assertOk()->assertSee('Shipments');
    }

    public function test_signs_in_with_a_real_email_as_the_username(): void
    {
        $this->staff(['email' => 'boss@example.com', 'username' => null]);
        $this->post('/staff/login', ['username' => 'BOSS@example.com', 'password' => 'a-long-password-1'])->assertRedirect('/staff');
    }

    public function test_every_failure_gets_the_same_message(): void
    {
        $this->staff(['username' => 'pema']);
        $this->staff(['username' => 'off', 'active' => false]);
        foreach ([['pema', 'wrong-password'], ['nobody', 'a-long-password-1'], ['off', 'a-long-password-1']] as [$u, $p]) {
            $this->post('/staff/login', ['username' => $u, 'password' => $p], ['X-Enhance' => '1'])
                ->assertOk()->assertSee("That username or password isn't right.");
            $this->assertGuest();
        }
    }

    public function test_empty_fields_are_reported(): void
    {
        $this->post('/staff/login', ['username' => '', 'password' => ''], ['X-Enhance' => '1'])
            ->assertSee('Enter your username.')->assertSee('Enter your password.');
    }

    public function test_too_many_wrong_passwords_are_slowed_down(): void
    {
        $this->staff(['username' => 'pema']);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/staff/login', ['username' => 'pema', 'password' => 'nope'], ['X-Enhance' => '1']);
        }
        $this->post('/staff/login', ['username' => 'pema', 'password' => 'a-long-password-1'], ['X-Enhance' => '1'])
            ->assertSee('Too many tries');
        $this->assertGuest();
    }

    public function test_a_temporary_password_must_be_changed_before_anything_else(): void
    {
        $user = $this->staff(['must_change_password' => true]);
        $this->post('/staff/login', ['username' => $user->username, 'password' => 'a-long-password-1'])->assertRedirect('/staff/password');
        $this->get('/staff')->assertRedirect('/staff/password');
        $this->get('/staff/clients')->assertRedirect('/staff/password');

        $this->post('/staff/password', ['password' => 'short', 'confirm' => 'short'], ['X-Enhance' => '1'])->assertSee('Use at least 10 characters.');
        $this->post('/staff/password', ['password' => 'a-brand-new-pass', 'confirm' => 'different-one-here'], ['X-Enhance' => '1'])->assertSee("The two passwords don't match.");
        $this->post('/staff/password', ['password' => 'a-brand-new-pass', 'confirm' => 'a-brand-new-pass'])->assertRedirect('/staff');
        $this->get('/staff')->assertOk();
        $this->assertFalse($user->fresh()->must_change_password);
    }

    public function test_a_switched_off_account_is_locked_out_immediately(): void
    {
        $user = $this->staff();
        $this->actingAs($user)->get('/staff')->assertOk();
        $user->update(['active' => false]);
        $this->get('/staff')->assertRedirect('/staff/login');
        $this->assertGuest();
    }

    public function test_sign_out(): void
    {
        $this->actingAs($this->staff())->post('/staff/logout')->assertRedirect('/staff/login');
        $this->assertGuest();
    }

    public function test_old_client_login_address_leads_to_staff_login(): void
    {
        $this->get('/login')->assertRedirect('/staff/login');
    }
}

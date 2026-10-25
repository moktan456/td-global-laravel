<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class AccountMenuTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_the_account_links_are_folded_away_in_a_menu_that_starts_closed(): void
    {
        $html = $this->actingAs($this->staff(['name' => 'Pema Dorji']))->get('/staff')->assertOk()->getContent();

        // the sidebar menu and the phone menu each have a closed account fold-out holding the three links
        $this->assertSame(2, substr_count($html, 'Account menu'));
        foreach (['Change password', 'View website', 'Sign out'] as $label) {
            $this->assertSame(2, substr_count($html, $label), "{$label} appears only inside the two account menus");
        }
        preg_match_all('#<details\s+(data-menu\s+)?class="group relative"[^>]*>#', $html, $m);
        $this->assertCount(2, $m[0]);
        foreach ($m[0] as $tag) {
            $this->assertStringNotContainsString(' open', $tag, 'closed by default');
        }
        $this->assertStringContainsString('data-menu', $m[0][0], 'the sidebar menu closes on Escape or a click elsewhere');
    }

    public function test_the_row_shows_initials_name_and_role(): void
    {
        $html = $this->actingAs($this->admin(['name' => 'Pema Dorji']))->get('/staff')->getContent();
        $this->assertStringContainsString('>PD</span>', $html);
        $this->assertStringContainsString('Pema Dorji', $html);
        $this->assertStringContainsString('Super admin', $html);

        $html = $this->actingAs($this->staff(['name' => 'Karma']))->get('/staff')->getContent();
        $this->assertStringContainsString('>K</span>', $html);
    }

    public function test_the_links_still_work(): void
    {
        $user = $this->staff();
        $this->actingAs($user)->get('/staff/password')->assertOk();
        $this->post('/staff/logout')->assertRedirect();
        $this->assertGuest();
    }

    public function test_the_menu_is_on_every_staff_page_but_not_on_the_sign_in_page(): void
    {
        $this->actingAs($this->staff());
        foreach (['/staff', '/staff/clients', '/staff/shipments/new', '/staff/password'] as $url) {
            $this->assertStringContainsString('Account menu', $this->get($url)->getContent(), $url);
        }
        auth()->logout();
        $this->assertStringNotContainsString('Account menu', $this->get('/staff/login')->getContent());
    }
}

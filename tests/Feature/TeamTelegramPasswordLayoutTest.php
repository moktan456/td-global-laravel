<?php

namespace Tests\Feature;

use App\Support\FormState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class TeamTelegramPasswordLayoutTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_the_team_list_is_one_row_per_person_with_a_manage_menu_and_badges(): void
    {
        $this->staff(['name' => 'Zangmo Tester', 'username' => 'zangmo.t', 'must_change_password' => true]);
        $this->staff(['name' => 'Switched Person', 'username' => 'off.p', 'active' => false]);
        $me = $this->admin(['name' => 'Boss Admin', 'username' => 'boss']);
        $html = $this->actingAs($me)->get('/staff/team')->assertOk()
            ->assertSee('Super admin')->assertSee('Active')->assertSee('Switched off')->assertSee('Temporary password')->assertSee('(you)')->getContent();

        $content = substr($html, strpos($html, '<main id="main"'));
        $this->assertSame(3, substr_count($content, '<details data-menu'), 'a Manage menu per person');
        foreach (['Set password', 'Switch off Zangmo Tester', 'Switch Switched Person back on'] as $control) {
            $at = strpos($content, $control);
            $this->assertNotFalse($at, $control);
            $this->assertNotFalse(strrpos(substr($content, 0, $at), '<details data-menu'), "{$control} is inside a menu");
        }
        $this->assertStringNotContainsString('Switch off Boss Admin', $html, 'you cannot switch yourself off');
    }

    public function test_adding_a_person_folds_away_until_needed(): void
    {
        $this->actingAs($this->admin());
        $closed = $this->get('/staff/team')->assertOk()->assertSee('Add a staff member')->getContent();
        preg_match('/<details id="add-staff"[^>]*>/', $closed, $m);
        $this->assertStringNotContainsString(' open', $m[0]);

        $opened = $this->withSession(['forms.team' => FormState::success('Added.')->toArray()])->get('/staff/team')->getContent();
        preg_match('/<details id="add-staff"[^>]*>/', $opened, $m);
        $this->assertStringContainsString(' open', $m[0]);
    }

    public function test_resetting_a_password_from_the_menu_still_works_in_place(): void
    {
        $person = $this->staff(['name' => 'Zangmo Tester', 'username' => 'zangmo.t']);
        $this->actingAs($this->admin())->post("/staff/team/{$person->id}/password", ['password' => 'a-fresh-temporary-1'], ['X-Enhance' => '1'])
            ->assertOk()->assertSee('New temporary password set');
    }

    public function test_the_telegram_page_says_when_it_needs_attention_and_when_it_is_ready(): void
    {
        config(['td.telegram.bot_token' => '', 'td.telegram.bot_username' => '', 'td.telegram.webhook_secret' => '']);
        $this->actingAs($this->admin())->get('/staff/telegram')->assertOk()->assertSee('need attention')->assertSee('Optional');

        config(['td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot', 'td.telegram.webhook_secret' => 'a-long-enough-secret-123']);
        Http::fake([
            'api.telegram.org/*getMe*' => Http::response(['ok' => true, 'result' => ['username' => 'td_test_bot']]),
            'api.telegram.org/*getWebhookInfo*' => Http::response(['ok' => true, 'result' => ['url' => rtrim(site('url'), '/').'/api/telegram/webhook', 'pending_update_count' => 0]]),
        ]);
        $this->get('/staff/telegram')->assertOk()->assertSee('Telegram is ready');
    }

    public function test_a_normal_password_change_lives_in_the_workspace(): void
    {
        $html = $this->actingAs($this->staff())->get('/staff/password')->assertOk()->assertSee('Change password')->getContent();
        $this->assertStringContainsString('Staff workspace', $html, 'the sidebar is there');
    }

    public function test_the_forced_first_change_is_the_plain_card_without_a_sidebar(): void
    {
        $html = $this->actingAs($this->staff(['must_change_password' => true]))->get('/staff/password')->assertOk()->assertSee('Choose your password')->assertSee('temporary')->getContent();
        $this->assertStringNotContainsString('Staff workspace', $html);
        $this->assertStringNotContainsString('/staff/clients', $html, 'nothing to wander off to yet');
    }
}

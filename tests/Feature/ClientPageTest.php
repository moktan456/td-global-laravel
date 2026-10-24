<?php

namespace Tests\Feature;

use App\Support\FormState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class ClientPageTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_a_client_name_in_the_list_opens_their_page(): void
    {
        $client = $this->client(['full_name' => 'Tashi Wangmo']);
        $this->actingAs($this->staff())->get('/staff/clients')->assertOk()->assertSee("/staff/clients/{$client->id}", false);
    }

    public function test_the_client_page_lists_only_their_requests_newest_first_with_links(): void
    {
        $tashi = $this->client(['full_name' => 'Tashi Wangmo', 'company' => 'Druk Motors']);
        $other = $this->client(['full_name' => 'Pema Choden']);
        $first = $this->shipment($tashi, ['goods' => 'Old consignment']);
        $second = $this->shipment($tashi, ['goods' => 'New consignment']);
        $theirs = $this->shipment($other, ['goods' => 'Not Tashi goods']);

        $html = $this->actingAs($this->staff())->get("/staff/clients/{$tashi->id}")->assertOk()
            ->assertSee('Tashi Wangmo')->assertSee('Druk Motors')->assertSee('Requests (2)')
            ->assertSee("/staff/shipments/{$first->tracking_number}", false)->assertSee("/staff/shipments/{$second->tracking_number}", false)
            ->assertDontSee('Not Tashi goods')->assertDontSee($theirs->tracking_number)
            ->assertSee("/staff/shipments/new?client={$tashi->id}", false)->getContent();
        $this->assertLessThan(strpos($html, 'Old consignment'), strpos($html, 'New consignment'), 'newest first');
    }

    public function test_a_client_with_no_requests_and_an_unknown_client(): void
    {
        $client = $this->client();
        $this->actingAs($this->staff());
        $this->get("/staff/clients/{$client->id}")->assertOk()->assertSee('Requests (0)')->assertSee('No requests yet');
        $this->get('/staff/clients/99999')->assertNotFound();
        $this->get('/staff/clients/abc')->assertNotFound();
    }

    public function test_a_long_request_history_is_paged(): void
    {
        $client = $this->client();
        for ($i = 1; $i <= 20; $i++) {
            $this->shipment($client, ['goods' => sprintf('Load %02d', $i)]);
        }
        $this->actingAs($this->staff());
        $this->get("/staff/clients/{$client->id}")->assertSee('Requests (20)')->assertSee('Load 20')->assertDontSee('Load 05')->assertSee('Page 1 of 2');
        $this->get("/staff/clients/{$client->id}?page=2")->assertSee('Load 05')->assertDontSee('Load 20');
    }

    public function test_the_page_needs_a_staff_sign_in(): void
    {
        $client = $this->client();
        $this->get("/staff/clients/{$client->id}")->assertRedirect('/staff/login');
    }

    public function test_new_request_from_a_client_page_starts_with_that_client_chosen(): void
    {
        $this->client(['full_name' => 'Someone Else']);
        $tashi = $this->client(['full_name' => 'Tashi Wangmo']);
        $html = $this->actingAs($this->staff())->get("/staff/shipments/new?client={$tashi->id}")->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<option value="'.$tashi->id.'"[^>]*selected#', $html);
        $this->actingAs($this->staff())->get('/staff/shipments/new?client[]=1')->assertOk();   // junk is ignored
    }

    public function test_the_tracking_number_tools_are_folded_away_until_needed(): void
    {
        $s = $this->shipment();
        $staff = $this->staff();

        $closed = $this->actingAs($staff)->get("/staff/shipments/{$s->tracking_number}")->assertOk()->assertSee('Client lost the tracking number?')->getContent();
        preg_match('/<details id="tracking-tools"[^>]*>/', $closed, $m);
        $this->assertNotEmpty($m);
        $this->assertStringNotContainsString(' open', $m[0], 'closed by default');

        $opened = $this->withSession(['forms.resend' => FormState::success('Sent again.')->toArray()])->get("/staff/shipments/{$s->tracking_number}")->getContent();
        preg_match('/<details id="tracking-tools"[^>]*>/', $opened, $m);
        $this->assertStringContainsString(' open', $m[0], 'open after a resend so the result is seen');
    }

    public function test_the_client_list_shows_a_status_and_keeps_the_telegram_tools_in_a_menu(): void
    {
        config(['td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot']);
        $linked = $this->client(['full_name' => 'Linked Person', 'telegram_chat_id' => 555]);
        $unlinked = $this->client(['full_name' => 'Unlinked Person']);
        $html = $this->actingAs($this->staff())->get('/staff/clients')->assertOk()
            ->assertSee('Connected')->assertSee('Not connected')->assertSee('Create Telegram link')->assertSee('Copy bot link')->getContent();

        $content = substr($html, strpos($html, '<main id="main"'));
        $this->assertSame(2, substr_count($content, '<details data-menu'), 'one Telegram menu per client');
        // the tools sit inside the menu panels, not loose in the list
        foreach (['Copy bot link', 'Create Telegram link', 'Create a new connect link'] as $tool) {
            $at = strpos($html, $tool);
            $this->assertNotFalse($at, $tool);
            $this->assertNotFalse(strrpos(substr($html, 0, $at), '<details data-menu'), "{$tool} is inside a menu");
        }
        $this->assertStringNotContainsString('Registered by', $html);
    }

    public function test_registering_a_client_folds_away_until_needed(): void
    {
        $closed = $this->actingAs($this->staff())->get('/staff/clients')->assertOk()->assertSee('Register a new client')->getContent();
        preg_match('/<details id="register-client"[^>]*>/', $closed, $m);
        $this->assertStringNotContainsString(' open', $m[0]);

        $opened = $this->withSession(['forms.client' => FormState::success('Client registered.')->toArray()])->get('/staff/clients')->getContent();
        preg_match('/<details id="register-client"[^>]*>/', $opened, $m);
        $this->assertStringContainsString(' open', $m[0], 'open after a submit so the result is seen');
    }

    public function test_staff_pages_use_the_workspace_frame_not_the_public_site_frame(): void
    {
        $this->actingAs($this->staff());
        foreach (['/staff' => 'Shipments', '/staff/clients' => 'Clients', '/staff/shipments/new' => 'New request'] as $url => $title) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertMatchesRegularExpression('#<h1[^>]*>'.preg_quote($title, '#').'</h1>#', $html, "{$url} has its own title");
            $this->assertStringContainsString('Staff workspace', $html);
            $this->assertStringNotContainsString('Request a quote', $html, 'no public footer');
            $this->assertStringNotContainsString('Cost calculator', $html, 'no public menu');
            $this->assertStringNotContainsString('on-dark bg-navy-900 text-white">', $html, 'no big banner');
        }
    }

    public function test_the_sign_in_page_is_a_plain_card(): void
    {
        $html = $this->get('/staff/login')->assertOk()->assertSee('Staff sign in')->getContent();
        $this->assertStringNotContainsString('Cost calculator', $html);
        $this->assertStringNotContainsString('Request a quote', $html);
        $this->assertStringNotContainsString('Staff workspace', $html, 'no sidebar before sign-in');
    }

    public function test_admin_links_show_only_to_super_admins(): void
    {
        $this->actingAs($this->staff())->get('/staff')->assertDontSee('/staff/team')->assertDontSee('/staff/telegram');
        $this->actingAs($this->admin())->get('/staff')->assertSee('/staff/team', false)->assertSee('/staff/telegram', false);
    }
}

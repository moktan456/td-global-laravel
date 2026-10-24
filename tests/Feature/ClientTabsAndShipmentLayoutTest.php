<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class ClientTabsAndShipmentLayoutTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    private function telegramOn(): void
    {
        config(['td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot']);
    }

    public function test_the_client_page_opens_on_requests_and_shows_a_summary(): void
    {
        $client = $this->client(['full_name' => 'Tashi Wangmo', 'email' => 'tashi@example.com', 'phone' => '+97517975757']);
        $this->shipment($client);
        $this->shipment($client, ['status' => 'released']);
        $html = $this->actingAs($this->staff())->get("/staff/clients/{$client->id}")->assertOk()
            ->assertSee('tashi@example.com')->assertSee('+97517975757')->assertSee('Requests (2)')->getContent();

        $this->assertMatchesRegularExpression('#In progress</dt><dd[^>]*>1</dd>#', $html);
        $this->assertMatchesRegularExpression('#aria-current="page"[^>]*>Requests \(2\)#', $html);
    }

    public function test_the_telegram_tab_holds_the_connect_tools_for_a_client_who_is_not_connected(): void
    {
        $this->telegramOn();
        $client = $this->client();
        $staff = $this->staff();
        $html = $this->actingAs($staff)->get("/staff/clients/{$client->id}?tab=telegram")->assertOk()
            ->assertSee('isn\'t connected yet', false)->assertSee('Create Telegram link')->assertDontSee('Copy bot link')->getContent();
        $this->assertMatchesRegularExpression('#aria-current="page"[^>]*>Telegram#', $html);

        $this->actingAs($staff)->post("/staff/clients/{$client->id}/telegram-link", [], ['X-Enhance' => '1'])->assertOk()->assertSee('https://t.me/td_test_bot?start=', false);
    }

    public function test_the_telegram_tab_for_a_connected_client_offers_the_bot_link_and_a_new_connect_link(): void
    {
        $this->telegramOn();
        $client = $this->client(['telegram_chat_id' => 555, 'telegram_linked_at' => now()]);
        $this->actingAs($this->staff())->get("/staff/clients/{$client->id}?tab=telegram")->assertOk()
            ->assertSee('is connected')->assertSee('Copy bot link')->assertSee('Create a new connect link');
    }

    public function test_the_details_tab_lists_the_contact_and_preferences(): void
    {
        $this->telegramOn();
        $client = $this->client(['full_name' => 'Tashi Wangmo', 'company' => 'Druk Motors', 'notify_email' => false]);
        $this->actingAs($this->staff())->get("/staff/clients/{$client->id}?tab=details")->assertOk()
            ->assertSee('Druk Motors')->assertSee('Email updates')->assertSee('Off (the client turned them off)')->assertSee('Not connected');
    }

    public function test_the_telegram_tab_is_hidden_when_telegram_is_not_set_up_and_bad_tabs_fall_back(): void
    {
        config(['td.telegram.bot_token' => '', 'td.telegram.bot_username' => '']);
        $client = $this->client();
        $this->actingAs($this->staff());
        $this->get("/staff/clients/{$client->id}")->assertOk()->assertDontSee('?tab=telegram', false);
        $this->get("/staff/clients/{$client->id}?tab=telegram")->assertOk()->assertSee('Requests (0)')->assertDontSee('Create Telegram link');
        $this->get("/staff/clients/{$client->id}?tab=nonsense")->assertOk()->assertSee('Requests (0)');
        $this->get("/staff/clients/{$client->id}?tab[]=x")->assertOk();
    }

    public function test_the_shipment_page_leads_with_the_stage_update_then_the_timeline_with_the_client_linked(): void
    {
        $client = $this->client(['full_name' => 'Tashi Wangmo', 'company' => 'Druk Motors']);
        $s = $this->shipment($client, ['details' => 'Fragile, keep dry']);
        $html = $this->actingAs($this->staff())->get("/staff/shipments/{$s->tracking_number}")->assertOk()
            ->assertSee('Update stage')->assertSee('Timeline')->assertSee('Activity')->assertDontSee('Notifications')
            ->assertSee('Notes on the request')->assertSee('Fragile, keep dry')->getContent();

        $this->assertLessThan(strpos($html, 'id="timeline-heading"'), strpos($html, 'id="update-heading"'), 'update first, then the timeline');
        $this->assertMatchesRegularExpression('#<a href="/staff/clients/'.$client->id.'"[^>]*>Druk Motors</a>#', $html);
        $this->assertStringContainsString('Client lost the tracking number?', $html);
    }

    public function test_the_request_created_banner_points_to_activity(): void
    {
        $s = $this->shipment();
        $this->actingAs($this->staff())->get("/staff/shipments/{$s->tracking_number}?new=1")->assertOk()->assertSee('See Activity below');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class StaffWorkflowTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_register_a_client_and_reject_duplicates(): void
    {
        $staff = $this->staff();
        $this->actingAs($staff)->post('/staff/clients', ['full_name' => 'Karma', 'email' => 'Karma@Example.com', 'phone' => '17975757'], ['X-Enhance' => '1'])
            ->assertSee('Karma is registered.');
        $this->assertDatabaseHas('clients', ['email' => 'karma@example.com', 'phone' => '+97517975757', 'registered_by' => 'staff']);

        $this->post('/staff/clients', ['full_name' => 'Other', 'email' => 'karma@example.com', 'phone' => '17000000'], ['X-Enhance' => '1'])
            ->assertSee('A client with this email or phone already exists.');
        $this->post('/staff/clients', ['full_name' => '', 'email' => 'bad', 'phone' => '1'], ['X-Enhance' => '1'])
            ->assertSee("Enter the client's name.")->assertSee('Enter an email address in the format')->assertSee("Enter the client's phone number");
    }

    public function test_creating_a_shipment_makes_a_tracking_number_a_timeline_entry_and_notifies_the_client(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $client = $this->client();
        $res = $this->actingAs($this->staff())->post('/staff/shipments', ['client_id' => $client->id, 'direction' => 'import', 'mode' => 'road', 'goods' => '2 pallets', 'details' => ''], ['X-Enhance' => '1']);
        $shipment = Shipment::firstOrFail();
        $res->assertNoContent()->assertHeader('X-Redirect', "/staff/shipments/{$shipment->tracking_number}?new=1");
        $this->assertSame('received', $shipment->status);
        $this->assertSame(1, $shipment->events()->count());
        Mail::assertSent(\App\Mail\PlainMessage::class, 1);
        $this->assertDatabaseHas('notification_log', ['shipment_id' => $shipment->id, 'kind' => 'tracking_number', 'status' => 'sent']);
        $this->get("/staff/shipments/{$shipment->tracking_number}?new=1")->assertOk()->assertSee('Request created.')->assertSee('Tracking number');
    }

    public function test_new_shipment_validation(): void
    {
        $this->actingAs($this->staff())->post('/staff/shipments', [], ['X-Enhance' => '1'])
            ->assertSee('Choose the client.')->assertSee('Choose import or export.')->assertSee('Choose how the goods are shipped.')->assertSee('Enter a short description of the goods.');
    }

    public function test_a_stage_change_is_logged_with_its_note_and_the_client_is_told(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $s = $this->shipment();
        $staff = $this->staff();
        $this->actingAs($staff)->post("/staff/shipments/{$s->tracking_number}/status", ['status' => 'documents_checked', 'note' => 'Looks fine', 'notify' => 'on'], ['X-Enhance' => '1'])
            ->assertSee('Status updated.')->assertSee('Email sent.');
        $s->refresh();
        $this->assertSame('documents_checked', $s->status);
        $events = $s->events;
        $this->assertCount(2, $events);
        $this->assertSame('Looks fine', $events->last()->note);
        $this->assertSame($staff->id, (int) $events->last()->created_by);
        Mail::assertSent(\App\Mail\PlainMessage::class, 1);
    }

    public function test_no_notification_when_the_box_is_not_ticked_and_a_note_alone_is_its_own_entry(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $s = $this->shipment();
        $this->actingAs($this->staff())->post("/staff/shipments/{$s->tracking_number}/status", ['status' => 'received', 'note' => 'Waiting for the driver'], ['X-Enhance' => '1']);
        Mail::assertNothingSent();
        $this->assertCount(2, $s->fresh()->events);
        $this->actingAs($this->staff())->post("/staff/shipments/{$s->tracking_number}/status", ['status' => 'bogus'], ['X-Enhance' => '1'])->assertSee('Choose a stage.');
    }

    public function test_email_not_set_up_is_shown_as_skipped_not_hidden(): void
    {
        config(['mail.default' => 'log']);
        $s = $this->shipment();
        $this->actingAs($this->staff())->post("/staff/shipments/{$s->tracking_number}/status", ['status' => 'cleared', 'notify' => 'on'], ['X-Enhance' => '1'])->assertSee('Email skipped');
        $this->assertDatabaseHas('notification_log', ['shipment_id' => $s->id, 'status' => 'skipped']);
        $this->get("/staff/shipments/{$s->tracking_number}")->assertSee('Skipped');
    }

    public function test_telegram_is_used_when_the_client_connected_it(): void
    {
        config(['mail.default' => 'smtp', 'td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot']);
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        $client = $this->client(['telegram_chat_id' => 555]);
        $s = $this->shipment($client);
        $this->actingAs($this->staff())->post("/staff/shipments/{$s->tracking_number}/status", ['status' => 'cleared', 'notify' => 'on'], ['X-Enhance' => '1'])
            ->assertSee('Email sent.')->assertSee('Telegram sent.');
        Http::assertSent(fn ($r) => str_contains($r->url(), '/botTESTTOKEN/sendMessage') && $r['chat_id'] === 555 && str_contains($r['text'], $s->tracking_number));
    }

    public function test_a_blocked_bot_disconnects_telegram_and_says_so(): void
    {
        config(['mail.default' => 'smtp', 'td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot']);
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'Forbidden: bot was blocked by the user'], 403)]);
        $client = $this->client(['telegram_chat_id' => 555]);
        $s = $this->shipment($client);
        $this->actingAs($this->staff())->post("/staff/shipments/{$s->tracking_number}/status", ['status' => 'cleared', 'notify' => 'on'], ['X-Enhance' => '1'])
            ->assertSee('Telegram failed')->assertSee('Telegram was disconnected');
        $this->assertNull($client->fresh()->telegram_chat_id);
    }

    public function test_dashboard_filters_by_stage_and_number(): void
    {
        $a = $this->shipment();
        $b = $this->shipment();
        $b->status = 'cleared';
        $b->save();
        $this->actingAs($this->staff());
        $this->get('/staff?status=cleared')->assertSee($b->tracking_number)->assertDontSee($a->tracking_number);
        $this->get('/staff?q='.strtolower(substr($a->tracking_number, -4)))->assertSee($a->tracking_number);
    }

    public function test_telegram_connect_links_are_one_time(): void
    {
        config(['td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot', 'td.telegram.webhook_secret' => 'a-long-enough-secret-123']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        $client = $this->client();
        $res = $this->actingAs($this->staff())->post("/staff/clients/{$client->id}/telegram-link", [], ['X-Enhance' => '1'])->assertOk();
        preg_match('#t\.me/td_test_bot\?start=([A-Za-z0-9_-]{43})#', $res->getContent(), $m);
        $this->assertNotEmpty($m[1] ?? null, 'a 43-character token is in the link');

        $headers = ['X-Telegram-Bot-Api-Secret-Token' => 'a-long-enough-secret-123'];
        $update = fn (string $text, int $chat = 777) => ['message' => ['text' => $text, 'chat' => ['id' => $chat, 'type' => 'private']]];
        $this->postJson('/api/telegram/webhook', $update("/start {$m[1]}"), $headers)->assertOk();
        $this->assertSame(777, $client->fresh()->telegram_chat_id);
        // A second person using the same link gets nothing.
        $this->postJson('/api/telegram/webhook', $update("/start {$m[1]}", 888), $headers)->assertOk();
        $this->assertSame(777, $client->fresh()->telegram_chat_id);
        $this->assertDatabaseHas('notification_log', ['client_id' => $client->id, 'kind' => 'telegram_linked']);

        $this->postJson('/api/telegram/webhook', $update('/stop'), $headers)->assertOk();
        $this->assertNull($client->fresh()->telegram_chat_id);
    }
}

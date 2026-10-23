<?php

namespace Tests\Feature;

use App\Models\NotificationLog;
use App\Models\TelegramLinkToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class ResendAndRelinkTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    private function telegramOn(): void
    {
        config(['mail.default' => 'smtp', 'td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot']);
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
    }

    public function test_staff_can_send_the_tracking_number_again(): void
    {
        $this->telegramOn();
        $client = $this->client(['telegram_chat_id' => 555]);
        $s = $this->shipment($client);

        $this->actingAs($this->staff())->post("/staff/shipments/{$s->tracking_number}/resend", [], ['X-Enhance' => '1'])
            ->assertOk()->assertSee('Sent again.')->assertSee('Telegram sent.');

        Http::assertSent(fn ($r) => str_contains($r->url(), '/sendMessage') && $r['chat_id'] === 555
            && str_contains($r['text'], "Your tracking number: <code>{$s->tracking_number}</code>") && str_contains($r['text'], 'Shipment: Import'));
        $this->assertSame(2, NotificationLog::where('shipment_id', $s->id)->where('kind', 'tracking_number')->where('status', 'sent')->count());   // Telegram and email
    }

    public function test_resending_says_so_when_nothing_could_be_delivered(): void
    {
        config(['mail.default' => 'log']);   // email not set up; the client has no Telegram
        $s = $this->shipment();
        $this->actingAs($this->staff())->post("/staff/shipments/{$s->tracking_number}/resend", [], ['X-Enhance' => '1'])
            ->assertOk()->assertSee('Not delivered.')->assertSee('send it yourself');
    }

    public function test_resending_is_limited_per_shipment(): void
    {
        $this->telegramOn();
        $s = $this->shipment($this->client(['telegram_chat_id' => 555]));
        RateLimiter::clear("resend-tracking:{$s->id}");
        $staff = $this->staff();
        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($staff)->post("/staff/shipments/{$s->tracking_number}/resend", [], ['X-Enhance' => '1'])->assertSee('Sent again.');
        }
        $this->actingAs($staff)->post("/staff/shipments/{$s->tracking_number}/resend", [], ['X-Enhance' => '1'])->assertSee('Already sent several times');
    }

    public function test_the_shipment_page_offers_copy_whatsapp_and_resend(): void
    {
        $client = $this->client(['phone' => '+97517975757']);
        $s = $this->shipment($client);
        $html = $this->actingAs($this->staff())->get("/staff/shipments/{$s->tracking_number}")->assertOk()
            ->assertSee('Send tracking number again')->assertSee('Copy number')->assertSee('Copy link')->getContent();
        $this->assertStringContainsString('https://wa.me/97517975757?text=', $html);
        $this->assertStringContainsString(rawurlencode($s->tracking_number), $html);
    }

    public function test_a_connected_client_gets_the_bot_link_and_can_be_given_a_new_connect_link(): void
    {
        $this->telegramOn();
        $client = $this->client(['telegram_chat_id' => 555]);
        $this->actingAs($this->staff())->get('/staff/clients')->assertOk()
            ->assertSee('https://t.me/td_test_bot', false)->assertSee('Copy bot link')->assertSee('Create a new connect link');
    }

    public function test_a_new_connect_link_replaces_earlier_unused_ones_and_offers_whatsapp(): void
    {
        $this->telegramOn();
        $client = $this->client(['phone' => '+97517975757']);
        $staff = $this->staff();

        $this->actingAs($staff)->post("/staff/clients/{$client->id}/telegram-link", [], ['X-Enhance' => '1'])->assertOk();
        $this->assertSame(1, TelegramLinkToken::where('client_id', $client->id)->count());

        $html = $this->actingAs($staff)->post("/staff/clients/{$client->id}/telegram-link", [], ['X-Enhance' => '1'])->assertOk()->getContent();
        $this->assertSame(1, TelegramLinkToken::where('client_id', $client->id)->whereNull('used_at')->count(), 'the earlier link no longer works');
        $this->assertStringContainsString('Copy link', $html);
        $this->assertStringContainsString('https://wa.me/97517975757?text=', $html);
        $this->assertStringContainsString('<svg', $html);
    }

    public function test_the_connect_link_answer_is_one_element_so_the_page_can_swap_it_in(): void
    {
        $this->telegramOn();
        $connected = $this->client(['telegram_chat_id' => 555]);
        $html = trim($this->actingAs($this->staff())->post("/staff/clients/{$connected->id}/telegram-link", [], ['X-Enhance' => '1'])->assertOk()->getContent());
        $this->assertStringStartsWith('<form id="tglink'.$connected->id.'-form"', $html);
        $this->assertStringEndsWith('</form>', $html);
        $this->assertStringNotContainsString('Copy bot link', $html, 'the bot link lives outside the swapped form');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Support\Qr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class QrAndTrackPageTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_qr_helper_returns_inline_svg_without_an_xml_prolog(): void
    {
        $svg = Qr::svg('https://t.me/example_bot?start=abc');
        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringNotContainsString('<?xml', $svg);
    }

    public function test_telegram_link_shows_a_qr_code_next_to_the_link(): void
    {
        config(['td.telegram.bot_token' => '123456:TESTTOKEN', 'td.telegram.bot_username' => 'example_bot']);
        $client = Client::create(['full_name' => 'Tashi', 'email' => 't@example.com', 'phone' => '+97517000009']);
        $this->actingAs($this->staff());

        $html = $this->post("/staff/clients/{$client->id}/telegram-link", [], ['X-Enhance' => '1'])->assertOk()->getContent();

        $this->assertStringContainsString('https://t.me/example_bot?start=', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('connect on Telegram', $html);
    }

    public function test_track_page_shows_the_example_card_until_a_number_is_looked_up(): void
    {
        $this->get('/track')->assertOk()->assertSee('What you&apos;ll see', false)->assertSee('TD-2610-SAMPLE')->assertSee('Lost your tracking number?');
        $this->post('/track', ['tracking' => 'TD-2610-NOPE99'], ['X-Enhance' => '1'])->assertOk()->assertSee('What you&apos;ll see', false);
    }
}

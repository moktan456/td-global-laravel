<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Support\Messages;
use App\Support\Statuses;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class TransportTrackPageTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    private function track(Shipment $s): string
    {
        return $this->post('/track', ['tracking' => $s->tracking_number], ['X-Enhance' => '1'])->assertOk()->getContent();
    }

    private function both(array $attrs = []): Shipment
    {
        return $this->shipment(null, $attrs + ['transport_enabled' => true]);
    }

    // ---------- the page

    public function test_a_customs_only_shipment_looks_exactly_as_before(): void
    {
        $s = $this->shipment(null, ['status' => 'pending_risk']);
        $html = $this->track($s);
        $this->assertStringContainsString('Pending Risk', $html);
        $this->assertStringNotContainsString('Step ', $html, 'no overview for one track');
        $this->assertStringNotContainsString('<h3', $html, 'no track headings for one track');
        $this->assertStringNotContainsString('Delivery', $html);
    }

    public function test_with_two_tracks_the_page_opens_with_an_overview_and_then_each_timeline_customs_first(): void
    {
        $s = $this->both(['status' => 'pending_risk', 'carrier' => 'Druk Carriers', 'expected_delivery' => '2026-11-02']);
        $s->update(['transport_status' => 'in_transit']);
        $html = $this->track($s);

        $this->assertStringContainsString('Step 3 of 7', $html);
        $this->assertStringContainsString('Step 4 of 6', $html);
        $this->assertStringContainsString('role="img" aria-label="Step 3 of 7"', $html);
        $this->assertStringContainsString('Expected 2 Nov 2026', $html);
        $this->assertLessThan(strpos($html, '>Transport</h3>'), strpos($html, '>Customs clearance</h3>'), 'customs comes first');
        $this->assertLessThan(strpos($html, '>Transport</h3>'), strpos($html, 'Step 3 of 7'), 'the overview comes before the timelines');
        $this->assertStringContainsString('Your shipment is on its way.', $html, 'the latest transport stage is explained in plain words');
    }

    public function test_each_timeline_shows_only_its_own_track(): void
    {
        $s = $this->both();
        $s->update(['transport_status' => 'in_transit']);
        $html = $this->track($s);
        $customs = substr($html, strpos($html, '>Customs clearance</h3>'), strpos($html, '>Transport</h3>') - strpos($html, '>Customs clearance</h3>'));
        $transport = substr($html, strpos($html, '>Transport</h3>'));

        $this->assertStringContainsString('Declaration Submitted', $customs);
        $this->assertStringNotContainsString('In Transit', $customs);
        $this->assertStringContainsString('In Transit', $transport);
        $this->assertStringContainsString('Order Placed', $transport);
        $this->assertStringNotContainsString('Declaration Submitted', substr($transport, strpos($transport, '<ol')), 'no customs entries in the transport timeline');
    }

    public function test_the_clients_delivery_details_are_shown_but_never_the_driver(): void
    {
        $s = $this->both(['carrier' => 'Druk Carriers', 'vehicle_number' => 'BP-1-A1234', 'pickup_place' => 'Phuentsholing', 'destination' => 'Gelephu', 'expected_delivery' => '2026-11-02', 'driver_name' => 'Secret Driver', 'driver_phone' => '+97517999888']);
        $html = $this->track($s);
        foreach (['Druk Carriers', 'BP-1-A1234', 'Phuentsholing', 'Gelephu', '2 Nov 2026'] as $shown) {
            $this->assertStringContainsString($shown, $html);
        }
        $this->assertStringNotContainsString('Secret Driver', $html);
        $this->assertStringNotContainsString('17999888', $html);
        $this->assertStringNotContainsString('Driver', $html);
    }

    public function test_the_details_box_is_left_out_when_nothing_was_filled_in(): void
    {
        $html = $this->track($this->both());
        $this->assertStringNotContainsString('Carrier', $html);
        $this->assertStringNotContainsString('Expected', $html);
    }

    public function test_once_delivered_the_expected_date_goes_and_the_proof_of_delivery_shows_in_the_timeline(): void
    {
        $s = $this->both(['expected_delivery' => '2026-11-02', 'status' => 'released']);
        $s->update(['transport_status' => 'delivered', 'delivered_to' => 'Tashi', 'delivered_at' => now()]);
        $s->events()->reorder('id', 'desc')->where('track', 'transport')->first()->update(['note' => 'Delivered to Tashi on 28 Oct 2026, 14:30.']);
        $html = $this->track($s);

        $this->assertStringNotContainsString('Expected', $html);
        $this->assertStringContainsString('Delivered to Tashi on 28 Oct 2026, 14:30.', $html);
        $this->assertSame(2, substr_count($html, 'Complete'), 'both tracks are complete');
    }

    public function test_a_transport_only_shipment_shows_its_stage_details_and_timeline_without_customs(): void
    {
        $s = $this->shipment(null, ['customs_enabled' => false, 'transport_enabled' => true, 'carrier' => 'Druk Carriers', 'destination' => 'Gelephu']);
        $s->update(['transport_status' => 'out_for_delivery']);
        $html = $this->track($s);

        $this->assertStringContainsString('Out for Delivery', $html);
        $this->assertStringContainsString('Druk Carriers', $html);
        $this->assertStringNotContainsString('Declaration Submitted', $html);
        $this->assertStringNotContainsString('Customs clearance</h3>', $html);
        $this->assertStringNotContainsString('Step ', $html, 'one track: no overview');
    }

    public function test_a_track_that_was_switched_off_disappears_for_the_client_but_comes_back_with_its_history(): void
    {
        $s = $this->both();
        $s->update(['transport_status' => 'in_transit']);
        $s->update(['transport_enabled' => false]);
        $html = $this->track($s);
        $this->assertStringNotContainsString('In Transit', $html);
        $this->assertStringNotContainsString('Transport', substr($html, strpos($html, 'id="result-heading"')));

        $s->update(['transport_enabled' => true]);
        $this->assertStringContainsString('In Transit', $this->track($s->fresh()));
    }

    public function test_entries_hidden_from_the_client_stay_hidden_on_either_track(): void
    {
        $s = $this->both();
        $s->events()->create(['track' => 'transport', 'status' => 'processing', 'note' => 'internal only', 'visible_to_client' => false]);
        $this->assertStringNotContainsString('internal only', $this->track($s));
    }

    public function test_a_link_from_a_message_opens_both_tracks_straight_away(): void
    {
        $s = $this->both();
        $s->update(['transport_status' => 'processing']);
        $this->get('/track?n='.$s->tracking_number)->assertOk()->assertSee('Step 2 of 6')->assertSee('Processing');
    }

    public function test_the_example_card_mentions_transport_for_clients_who_have_it(): void
    {
        $this->get('/track')->assertOk()->assertSee('If your shipment includes transport', false);
    }

    // ---------- words in messages and lists

    public function test_the_stage_summary_names_each_track_when_there_are_two(): void
    {
        $s = $this->both(['status' => 'pending_risk']);
        $s->update(['transport_status' => 'in_transit']);
        $this->assertSame('Customs: Pending Risk · Transport: In Transit', $s->stageSummary());
        $this->assertSame('Declaration Submitted', $this->shipment()->stageSummary());
        $this->assertSame('Order Placed', $this->shipment(null, ['customs_enabled' => false, 'transport_enabled' => true])->stageSummary());
    }

    public function test_the_tracking_number_message_says_what_is_being_tracked_only_when_transport_is_in_it(): void
    {
        $customs = Messages::telegramTrackingNumber('Tashi', 'TD-2610-K7QX4M', 'Import · Road freight · Rice', ['customs']);
        $this->assertStringContainsString("we've received your clearance request", $customs);
        $this->assertStringNotContainsString('keep you updated', $customs);
        $this->assertStringNotContainsString('keep you updated', Messages::telegramTrackingNumber('Tashi', 'TD-2610-K7QX4M'), 'the old calls are unchanged');

        $both = Messages::telegramTrackingNumber('Tashi', 'TD-2610-K7QX4M', null, ['customs', 'transport']);
        $this->assertStringContainsString('We will keep you updated on: customs clearance and transport.', $both);

        $transport = Messages::telegramTrackingNumber('Tashi', 'TD-2610-K7QX4M', null, ['transport']);
        $this->assertStringContainsString("we've received your transport request", $transport);
        $this->assertStringContainsString('keep you updated on: transport.', $transport);

        $email = Messages::trackingNumberEmail('Tashi', 'TD-2610-K7QX4M', null, ['customs', 'transport'])['text'];
        $this->assertStringContainsString('We will keep you updated on: customs clearance and transport.', $email);
        $this->assertStringNotContainsString('keep you updated', Messages::trackingNumberEmail('Tashi', 'TD-2610-K7QX4M', null, ['customs'])['text']);
    }

    public function test_a_new_request_with_transport_tells_the_client_in_the_first_message(): void
    {
        config(['mail.default' => 'smtp', 'td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot']);
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        $client = $this->client(['telegram_chat_id' => 555]);

        $this->actingAs($this->staff())->post('/staff/shipments', ['client_id' => $client->id, 'direction' => 'import', 'mode' => 'road', 'goods' => 'Rice', 'details' => '', 'tracks_chosen' => '1', 'track_customs' => '1', 'track_transport' => '1'], ['X-Enhance' => '1'])->assertNoContent();
        Http::assertSent(fn ($r) => str_contains($r['text'] ?? '', 'We will keep you updated on: customs clearance and transport.'));
    }

    public function test_a_transport_update_carries_the_expected_delivery_date_until_it_is_delivered(): void
    {
        config(['mail.default' => 'smtp', 'td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot']);
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        $s = $this->shipment($this->client(['telegram_chat_id' => 555]), ['transport_enabled' => true, 'expected_delivery' => '2026-11-02']);
        $this->actingAs($this->staff());

        $this->post("/staff/shipments/{$s->tracking_number}/transport-status", ['status' => 'in_transit', 'notify' => 'on'], ['X-Enhance' => '1'])->assertOk();
        Http::assertSent(fn ($r) => str_contains($r['text'] ?? '', 'New transport stage: <b>In Transit</b>') && str_contains($r['text'], 'Expected delivery: 2 Nov 2026.'));

        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        $this->post("/staff/shipments/{$s->tracking_number}/transport-status", ['status' => 'delivered', 'delivered_to' => 'Tashi', 'notify' => 'on'], ['X-Enhance' => '1'])->assertOk();
        Http::assertSent(fn ($r) => str_contains($r['text'] ?? '', 'Delivered') && ! str_contains($r['text'], 'Expected delivery'));
    }

    public function test_the_status_message_without_an_expected_date_is_as_before(): void
    {
        $text = Messages::telegramStatusUpdate('Tashi', 'TD-2610-K7QX4M', Statuses::info('in_transit'), null, null, 'transport');
        $this->assertStringNotContainsString('Expected delivery', $text);
        $this->assertStringContainsString('Expected delivery: 2 Nov 2026.', Messages::telegramStatusUpdate('Tashi', 'TD-2610-K7QX4M', Statuses::info('in_transit'), null, null, 'transport', '2 Nov 2026'));
        $this->assertStringContainsString('Expected delivery: 2 Nov 2026.', Messages::statusUpdateEmail('Tashi', 'TD-2610-K7QX4M', 'in_transit', null, null, 'transport', '2 Nov 2026')['text']);
    }

    public function test_the_telegram_status_command_shows_both_stages(): void
    {
        config(['td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot', 'td.telegram.webhook_secret' => 'a-long-enough-secret-123']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        $s = $this->shipment($this->client(['telegram_chat_id' => 555]), ['transport_enabled' => true, 'status' => 'pending_risk']);
        $s->update(['transport_status' => 'in_transit']);

        $this->postJson('/api/telegram/webhook', ['update_id' => 1, 'message' => ['chat' => ['id' => 555, 'type' => 'private'], 'text' => '/status']], ['X-Telegram-Bot-Api-Secret-Token' => 'a-long-enough-secret-123'])->assertOk();
        Http::assertSent(fn ($r) => str_contains($r['text'] ?? '', 'Customs: Pending Risk · Transport: In Transit'));
    }
}

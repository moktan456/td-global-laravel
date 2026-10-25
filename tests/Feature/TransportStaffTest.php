<?php

namespace Tests\Feature;

use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class TransportStaffTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    private function post_(string $url, array $data): TestResponse
    {
        return $this->post($url, $data, ['X-Enhance' => '1']);
    }

    private function newRequest(array $extra = []): array
    {
        return array_merge(['client_id' => $this->client()->id, 'direction' => 'import', 'mode' => 'road', 'goods' => 'Rice', 'details' => '', 'tracks_chosen' => '1', 'track_customs' => '1'], $extra);
    }

    // ---------- New request

    public function test_the_new_request_form_has_two_switches_customs_on_and_transport_off(): void
    {
        $html = $this->actingAs($this->staff())->get('/staff/shipments/new')->assertOk()->assertSee('What should we track?')->getContent();
        $this->assertMatchesRegularExpression('#name="track_customs"[^>]*checked#', $html);
        $this->assertDoesNotMatchRegularExpression('#name="track_transport"[^>]*checked#', $html);
        $this->assertStringContainsString('role="switch"', $html);
        $this->assertStringContainsString('name="driver_phone"', $html, 'the details are there, shown when transport is switched on');
        $this->assertStringContainsString('Driver (staff only)', $html);
    }

    public function test_a_request_can_follow_customs_only_transport_only_or_both(): void
    {
        $this->actingAs($this->staff());
        $this->post_('/staff/shipments', $this->newRequest())->assertNoContent();
        $this->post_('/staff/shipments', $this->newRequest(['track_customs' => '', 'track_transport' => '1']))->assertNoContent();
        $this->post_('/staff/shipments', $this->newRequest(['track_transport' => '1']))->assertNoContent();

        [$a, $b, $c] = Shipment::orderBy('id')->get()->all();
        $this->assertSame(['customs'], $a->tracks());
        $this->assertSame(['transport'], $b->tracks());
        $this->assertNull($b->status);
        $this->assertSame(['customs', 'transport'], $c->tracks());
        $this->assertSame('order_placed', $c->transport_status);
    }

    public function test_choosing_nothing_to_track_is_refused_and_keeps_what_was_typed(): void
    {
        $this->actingAs($this->staff())->post_('/staff/shipments', $this->newRequest(['track_customs' => '', 'goods' => 'Cardamom']))
            ->assertOk()->assertSee('Choose what to track')->assertSee('Cardamom');
        $this->assertSame(0, Shipment::count());
    }

    public function test_a_request_without_the_form_marker_is_customs_only_as_before(): void
    {
        $data = $this->newRequest();
        unset($data['tracks_chosen'], $data['track_customs']);
        $this->actingAs($this->staff())->post_('/staff/shipments', $data)->assertNoContent();
        $this->assertSame(['customs'], Shipment::firstOrFail()->tracks());
    }

    public function test_transport_details_are_saved_with_the_request_and_checked_only_when_transport_is_on(): void
    {
        $this->actingAs($this->staff());
        $this->post_('/staff/shipments', $this->newRequest(['track_transport' => '1', 'carrier' => 'Druk Carriers', 'vehicle_number' => 'BP-1-A1234', 'pickup_place' => 'Phuentsholing', 'destination' => 'Gelephu', 'expected_delivery' => '2026-11-02', 'driver_name' => 'Sonam', 'driver_phone' => '+975 17 123 456']))->assertNoContent();
        $s = Shipment::firstOrFail();
        $this->assertSame(['Druk Carriers', 'BP-1-A1234', 'Phuentsholing', 'Gelephu', '2026-11-02', 'Sonam', '+975 17 123 456'], [$s->carrier, $s->vehicle_number, $s->pickup_place, $s->destination, $s->expected_delivery->format('Y-m-d'), $s->driver_name, $s->driver_phone]);

        $this->post_('/staff/shipments', $this->newRequest(['track_transport' => '1', 'expected_delivery' => '31/02/2026']))->assertOk()->assertSee('Enter the date');
        $this->post_('/staff/shipments', $this->newRequest(['track_transport' => '1', 'driver_phone' => 'call me']))->assertOk()->assertSee('Use digits');
        $this->assertSame(1, Shipment::count());

        // with transport off the details are not looked at, and not stored
        $this->post_('/staff/shipments', $this->newRequest(['expected_delivery' => 'garbage', 'carrier' => 'Ignored']))->assertNoContent();
        $this->assertNull(Shipment::orderByDesc('id')->first()->carrier);
    }

    // ---------- What we track (on a shipment)

    public function test_the_shipment_page_shows_a_tab_per_track_with_the_current_stage(): void
    {
        $s = $this->shipment(null, ['transport_enabled' => true]);
        $s->update(['transport_status' => 'in_transit']);
        $html = $this->actingAs($this->staff())->get("/staff/shipments/{$s->tracking_number}")->assertOk()
            ->assertSee('What we track')->assertSee('Customs clearance')->assertSee('Transport')->getContent();
        $this->assertStringContainsString('>Declaration Submitted</span>', $html);
        $this->assertStringContainsString('>In Transit</span>', $html);
        $this->assertMatchesRegularExpression('#aria-current="page"[^>]*>\s*Customs clearance#', $html, 'customs is the open tab');
        $this->assertStringContainsString('Customs clearance</span>', $this->get("/staff/shipments/{$s->tracking_number}?track=transport")->getContent(), 'the header shows a badge per track');
    }

    public function test_the_transport_tab_shows_the_transport_form_and_an_off_track_offers_to_switch_on(): void
    {
        $both = $this->shipment(null, ['transport_enabled' => true]);
        $this->actingAs($this->staff())->get("/staff/shipments/{$both->tracking_number}?track=transport")->assertOk()
            ->assertSee('Transport stage')->assertSee('Proof of delivery')->assertSee('Ready for Pickup')->assertSee('Out for Delivery');

        $customsOnly = $this->shipment();
        $this->get("/staff/shipments/{$customsOnly->tracking_number}?track=transport")->assertOk()
            ->assertSee('Transport is not tracked for this shipment.')->assertSee('Switch on transport tracking');

        $transportOnly = $this->shipment(null, ['customs_enabled' => false, 'transport_enabled' => true]);
        $this->get("/staff/shipments/{$transportOnly->tracking_number}")->assertOk()->assertSee('Transport stage')->assertSee('Proof of delivery', false);
        $this->get("/staff/shipments/{$transportOnly->tracking_number}?track=customs")->assertSee('Customs clearance is not tracked for this shipment.');
    }

    public function test_staff_can_switch_transport_on_and_off_and_history_is_kept(): void
    {
        $s = $this->shipment();
        $this->actingAs($this->staff());

        $this->post_("/staff/shipments/{$s->tracking_number}/tracks", ['track_customs' => '1', 'track_transport' => '1'])->assertOk()->assertSee('Now tracking: Customs clearance and Transport.');
        $this->assertSame('order_placed', $s->fresh()->transport_status);

        $s->update(['transport_status' => 'in_transit']);
        $this->post_("/staff/shipments/{$s->tracking_number}/tracks", ['track_customs' => '1'])->assertOk()->assertSee('Now tracking: Customs clearance.');
        $this->assertFalse($s->fresh()->transport_enabled);
        $this->assertSame('in_transit', $s->fresh()->transport_status, 'kept');

        $this->post_("/staff/shipments/{$s->tracking_number}/tracks", ['track_customs' => '1', 'track_transport' => '1'])->assertOk();
        $this->assertSame('in_transit', $s->fresh()->transport_status, 'resumes where it was');
    }

    public function test_the_last_track_cannot_be_switched_off(): void
    {
        $s = $this->shipment();
        $this->actingAs($this->staff())->post_("/staff/shipments/{$s->tracking_number}/tracks", [])->assertOk()->assertSee('Keep at least one switched on');
        $this->assertTrue($s->fresh()->customs_enabled);
    }

    public function test_switching_customs_off_for_a_shipment_that_has_transport_works(): void
    {
        $s = $this->shipment(null, ['transport_enabled' => true]);
        $this->actingAs($this->staff())->post_("/staff/shipments/{$s->tracking_number}/tracks", ['track_transport' => '1'])->assertOk()->assertSee('Now tracking: Transport.');
        $s = $s->fresh();
        $this->assertFalse($s->customs_enabled);
        $this->assertSame(['transport'], $s->tracks());
    }

    // ---------- Transport stage and proof of delivery

    public function test_staff_move_the_transport_stage_with_a_note_and_the_customs_stage_is_untouched(): void
    {
        $s = $this->shipment(null, ['transport_enabled' => true]);
        $this->actingAs($this->staff())->post_("/staff/shipments/{$s->tracking_number}/transport-status", ['status' => 'in_transit', 'note' => 'Left Phuentsholing'])
            ->assertOk()->assertSee('Transport updated.');

        $s = $s->fresh();
        $this->assertSame('in_transit', $s->transport_status);
        $this->assertSame('declaration_submitted', $s->status);
        $event = $s->events->where('track', 'transport')->last();
        $this->assertSame(['in_transit', 'Left Phuentsholing'], [$event->status, $event->note]);
        $this->assertSame(1, $s->events->where('track', 'customs')->count(), 'nothing new on the customs side');
    }

    public function test_the_client_is_told_which_track_moved(): void
    {
        config(['mail.default' => 'smtp', 'td.telegram.bot_token' => 'TESTTOKEN', 'td.telegram.bot_username' => 'td_test_bot']);
        Mail::fake();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        $s = $this->shipment($this->client(['telegram_chat_id' => 555]), ['transport_enabled' => true]);

        $this->actingAs($this->staff())->post_("/staff/shipments/{$s->tracking_number}/transport-status", ['status' => 'out_for_delivery', 'notify' => 'on'])
            ->assertOk()->assertSee('Telegram sent.');
        Http::assertSent(fn ($r) => str_contains($r['text'] ?? '', 'New transport stage: <b>Out for Delivery</b>'));
    }

    public function test_no_message_goes_out_unless_notify_is_ticked(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $s = $this->shipment(null, ['transport_enabled' => true]);
        $this->actingAs($this->staff())->post_("/staff/shipments/{$s->tracking_number}/transport-status", ['status' => 'processing'])->assertOk();
        Mail::assertNothingSent();
    }

    public function test_delivered_records_who_received_it_and_when_in_bhutan_time_and_the_note_for_the_client_says_so(): void
    {
        $s = $this->shipment(null, ['transport_enabled' => true]);
        $this->actingAs($this->staff())->post_("/staff/shipments/{$s->tracking_number}/transport-status", [
            'status' => 'delivered', 'delivered_to' => 'Tashi Wangmo', 'delivered_at' => '2026-10-28T14:30', 'note' => 'Signed delivery note',
        ])->assertOk()->assertSee('Transport updated.');

        $s = $s->fresh();
        $this->assertSame('Tashi Wangmo', $s->delivered_to);
        $this->assertSame('2026-10-28 08:30', $s->delivered_at->utc()->format('Y-m-d H:i'), '14:30 in Bhutan is 08:30 UTC');
        $note = $s->events->where('track', 'transport')->last()->note;
        $this->assertSame('Delivered to Tashi Wangmo on 28 Oct 2026, 14:30. Signed delivery note', $note);
        $this->actingAs($this->staff())->get("/staff/shipments/{$s->tracking_number}")->assertSee('Delivered to Tashi Wangmo on 28 Oct 2026, 14:30.');
    }

    public function test_delivered_works_with_no_receiver_and_defaults_the_time_to_now(): void
    {
        $s = $this->shipment(null, ['transport_enabled' => true]);
        $this->actingAs($this->staff())->post_("/staff/shipments/{$s->tracking_number}/transport-status", ['status' => 'delivered'])->assertOk()->assertSee('Transport updated.');
        $s = $s->fresh();
        $this->assertNull($s->delivered_to);
        $this->assertNotNull($s->delivered_at);
        $this->assertStringStartsWith('Delivered on ', $s->events->where('track', 'transport')->last()->note);
    }

    public function test_bad_proof_of_delivery_input_is_refused_and_nothing_changes(): void
    {
        $s = $this->shipment(null, ['transport_enabled' => true]);
        $this->actingAs($this->staff());
        $this->post_("/staff/shipments/{$s->tracking_number}/transport-status", ['status' => 'delivered', 'delivered_at' => 'yesterday-ish'])->assertSee('Enter the date and time of delivery.');
        $this->post_("/staff/shipments/{$s->tracking_number}/transport-status", ['status' => 'delivered', 'delivered_to' => str_repeat('x', 121)])->assertSee('Use 120 characters or fewer.');
        $this->assertSame('order_placed', $s->fresh()->transport_status);
    }

    public function test_moving_away_from_delivered_clears_the_proof_and_a_note_alone_is_its_own_entry(): void
    {
        $s = $this->shipment(null, ['transport_enabled' => true]);
        $this->actingAs($this->staff());
        $this->post_("/staff/shipments/{$s->tracking_number}/transport-status", ['status' => 'delivered', 'delivered_to' => 'Tashi'])->assertOk();
        $entries = $s->fresh()->events->where('track', 'transport')->count();

        $this->post_("/staff/shipments/{$s->tracking_number}/transport-status", ['status' => 'delivered', 'delivered_to' => 'Tashi', 'note' => 'Photo kept on file'])->assertOk();
        $this->assertSame($entries + 1, $s->fresh()->events->where('track', 'transport')->count(), 'same stage, new note');

        $this->post_("/staff/shipments/{$s->tracking_number}/transport-status", ['status' => 'out_for_delivery'])->assertOk();
        $s = $s->fresh();
        $this->assertNull($s->delivered_to);
        $this->assertNull($s->delivered_at);
    }

    public function test_the_transport_stage_cannot_be_set_to_a_customs_stage_or_when_transport_is_off(): void
    {
        $s = $this->shipment(null, ['transport_enabled' => true]);
        $this->actingAs($this->staff());
        $this->post_("/staff/shipments/{$s->tracking_number}/transport-status", ['status' => 'pending_risk'])->assertSee('Choose a stage.');
        $this->post_("/staff/shipments/{$s->tracking_number}/status", ['status' => 'in_transit'])->assertSee('Choose a stage.');

        $off = $this->shipment();
        $this->post_("/staff/shipments/{$off->tracking_number}/transport-status", ['status' => 'in_transit'])->assertSee('switched off');
        $this->assertNull($off->fresh()->transport_status);

        $transportOnly = $this->shipment(null, ['customs_enabled' => false, 'transport_enabled' => true]);
        $this->post_("/staff/shipments/{$transportOnly->tracking_number}/status", ['status' => 'pending_risk'])->assertSee('Customs tracking is switched off');
        $this->assertNull($transportOnly->fresh()->status);
    }

    public function test_the_stage_pages_need_a_sign_in_and_unknown_shipments_are_404(): void
    {
        $s = $this->shipment(null, ['transport_enabled' => true]);
        foreach (['tracks', 'transport-status', 'transport-details'] as $action) {
            $this->post("/staff/shipments/{$s->tracking_number}/{$action}", [])->assertRedirect('/staff/login');
        }
        $this->actingAs($this->staff());
        foreach (['tracks', 'transport-status', 'transport-details'] as $action) {
            $this->post_("/staff/shipments/TD-0000-ZZZZZZ/{$action}", [])->assertNotFound();
        }
    }

    // ---------- Transport details

    public function test_staff_can_edit_the_transport_details_without_touching_the_timeline(): void
    {
        $s = $this->shipment(null, ['transport_enabled' => true]);
        $before = $s->events()->count();
        $this->actingAs($this->staff())->post_("/staff/shipments/{$s->tracking_number}/transport-details", [
            'carrier' => 'Druk Carriers', 'vehicle_number' => 'BP-1-A1234', 'pickup_place' => 'Phuentsholing', 'destination' => 'Gelephu', 'expected_delivery' => '2026-11-02', 'driver_name' => 'Sonam', 'driver_phone' => '17123456',
        ])->assertOk()->assertSee('Transport details saved.');

        $s = $s->fresh();
        $this->assertSame(['Druk Carriers', 'Gelephu', 'Sonam'], [$s->carrier, $s->destination, $s->driver_name]);
        $this->assertSame($before, $s->events()->count());
        $this->post_("/staff/shipments/{$s->tracking_number}/transport-details", ['carrier' => ''])->assertSee('saved');
        $this->assertNull($s->fresh()->carrier, 'a cleared field is cleared');
    }

    public function test_bad_details_are_refused_with_a_reason_and_keep_what_was_typed(): void
    {
        $s = $this->shipment(null, ['transport_enabled' => true]);
        $this->actingAs($this->staff())->post_("/staff/shipments/{$s->tracking_number}/transport-details", ['carrier' => 'Druk', 'driver_phone' => 'call me', 'expected_delivery' => 'soon'])
            ->assertOk()->assertSee('Use digits, spaces')->assertSee('Enter the date')->assertSee('Druk');
        $this->assertNull($s->fresh()->carrier);
    }

    public function test_the_details_card_only_appears_when_transport_is_tracked(): void
    {
        $this->actingAs($this->staff());
        $this->get('/staff/shipments/'.$this->shipment()->tracking_number)->assertDontSee('Transport details');
        $this->get('/staff/shipments/'.$this->shipment(null, ['transport_enabled' => true])->tracking_number)->assertSee('Transport details');
    }

    // ---------- What the timeline shows, and what stays private

    public function test_the_timeline_tags_each_entry_with_its_track_when_there_are_two(): void
    {
        $both = $this->shipment(null, ['transport_enabled' => true]);
        $html = $this->actingAs($this->staff())->get("/staff/shipments/{$both->tracking_number}")->getContent();
        $timeline = substr($html, strpos($html, 'id="timeline-heading"'));
        $this->assertStringContainsString('>Customs clearance</span>', $timeline);
        $this->assertStringContainsString('>Transport</span>', $timeline);

        $one = $this->shipment();
        $timeline = substr($this->get("/staff/shipments/{$one->tracking_number}")->getContent(), strpos($this->get("/staff/shipments/{$one->tracking_number}")->getContent(), 'id="timeline-heading"'));
        $this->assertStringNotContainsString('rounded-full bg-sand px-2 py-0.5 text-xs font-semibold text-muted', $timeline, 'no tags when there is only one track');
    }

    public function test_the_driver_and_vehicle_details_are_not_on_the_public_track_page_yet(): void
    {
        $s = $this->shipment(null, ['transport_enabled' => true, 'driver_name' => 'Secret Driver', 'driver_phone' => '+97517999888']);
        $this->post('/track', ['tracking' => $s->tracking_number], ['X-Enhance' => '1'])->assertOk()->assertDontSee('Secret Driver')->assertDontSee('17999888');
    }
}

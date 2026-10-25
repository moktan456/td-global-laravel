<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Support\Statuses;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class TransportFoundationTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    private function transportOnly(array $attrs = []): Shipment
    {
        return $this->shipment(null, $attrs + ['customs_enabled' => false, 'transport_enabled' => true]);
    }

    private function both(array $attrs = []): Shipment
    {
        return $this->shipment(null, $attrs + ['transport_enabled' => true]);
    }

    // ---- the stages

    public function test_there_are_six_transport_stages_in_journey_order(): void
    {
        $this->assertSame(
            ['Order Placed', 'Processing', 'Ready for Pickup', 'In Transit', 'Out for Delivery', 'Delivered'],
            array_column(Statuses::transport(), 'label'),
        );
        foreach (Statuses::transport() as $stage) {
            $this->assertNotSame('', $stage['clientText']);
        }
        $this->assertSame(Statuses::transport(), Statuses::forTrack('transport'));
        $this->assertSame(Statuses::all(), Statuses::forTrack('customs'));
    }

    public function test_stage_keys_are_unique_across_both_tracks_so_a_key_names_its_track(): void
    {
        $all = array_merge(Statuses::keysFor('customs'), Statuses::keysFor('transport'));
        $this->assertSame($all, array_unique($all));
        $this->assertSame('customs', Statuses::trackOf('pending_risk'));
        $this->assertSame('transport', Statuses::trackOf('in_transit'));
        $this->assertNull(Statuses::trackOf('nonsense'));
        $this->assertNull(Statuses::trackOf(null));
        $this->assertTrue(Statuses::existsIn('transport', 'delivered'));
        $this->assertFalse(Statuses::existsIn('customs', 'delivered'));
    }

    public function test_the_existing_customs_helpers_still_mean_customs(): void
    {
        $this->assertTrue(Statuses::exists('released'));
        $this->assertFalse(Statuses::exists('in_transit'), 'the customs forms and filters never accept a transport stage');
        $this->assertSame(Statuses::keysFor('customs'), Statuses::keys());
    }

    public function test_info_finds_a_stage_on_either_track_and_first_last_and_position_work(): void
    {
        $this->assertSame('In Transit', Statuses::info('in_transit')['label']);
        $this->assertSame('Pending Risk', Statuses::info('pending_risk')['label']);
        $this->assertSame('declaration_submitted', Statuses::first('customs'));
        $this->assertSame('released', Statuses::last('customs'));
        $this->assertSame('order_placed', Statuses::first('transport'));
        $this->assertSame('delivered', Statuses::last('transport'));
        $this->assertSame([4, 6], Statuses::position('in_transit'));
        $this->assertSame([3, 7], Statuses::position('pending_risk'));
        $this->assertNull(Statuses::position('nonsense'));
        $this->assertSame('Customs clearance', Statuses::trackLabel('customs'));
        $this->assertSame('Transport', Statuses::trackLabel('transport'));
    }

    // ---- a new shipment

    public function test_a_normal_shipment_is_customs_only_exactly_as_before(): void
    {
        $s = $this->shipment()->fresh();
        $this->assertTrue($s->customs_enabled);
        $this->assertFalse($s->transport_enabled);
        $this->assertSame('declaration_submitted', $s->status);
        $this->assertNull($s->transport_status);
        $this->assertSame(['customs'], $s->tracks());
        $this->assertSame([['customs', 'declaration_submitted']], $s->events->map(fn ($e) => [$e->track, $e->status])->all());
    }

    public function test_a_transport_only_shipment_has_no_customs_stage_and_starts_at_order_placed(): void
    {
        $s = $this->transportOnly()->fresh();
        $this->assertNull($s->status);
        $this->assertSame('order_placed', $s->transport_status);
        $this->assertSame(['transport'], $s->tracks());
        $this->assertSame([['transport', 'order_placed']], $s->events->map(fn ($e) => [$e->track, $e->status])->all());
    }

    public function test_a_shipment_on_both_tracks_starts_each_at_its_first_stage(): void
    {
        $s = $this->both()->fresh();
        $this->assertSame(['customs', 'transport'], $s->tracks());
        $this->assertSame('declaration_submitted', $s->status);
        $this->assertSame('order_placed', $s->transport_status);
        $this->assertEqualsCanonicalizing([['customs', 'declaration_submitted'], ['transport', 'order_placed']], $s->events->map(fn ($e) => [$e->track, $e->status])->all());
    }

    // ---- moving along

    public function test_each_track_logs_its_own_changes(): void
    {
        $s = $this->both();
        $s->update(['transport_status' => 'in_transit']);
        $s->update(['status' => 'pending_risk']);

        $events = $s->fresh()->events->map(fn ($e) => [$e->track, $e->status])->all();
        $this->assertContains(['transport', 'in_transit'], $events);
        $this->assertContains(['customs', 'pending_risk'], $events);
        $this->assertCount(4, $events, 'two starting entries and one per change, nothing duplicated');
    }

    public function test_changing_something_else_adds_nothing_to_the_timeline(): void
    {
        $s = $this->both();
        $before = $s->events()->count();
        $s->update(['carrier' => 'Druk Carriers', 'vehicle_number' => 'BP-1-A1234', 'assigned_to' => null, 'expected_delivery' => '2026-11-02']);
        $this->assertSame($before, $s->events()->count());
        $this->assertSame('2026-11-02', $s->fresh()->expected_delivery->format('Y-m-d'));
    }

    public function test_logging_a_note_on_a_track_records_that_tracks_stage(): void
    {
        $s = $this->both();
        $s->update(['transport_status' => 'in_transit']);
        $event = $s->logStage('Left Phuentsholing', 'transport');
        $this->assertSame(['transport', 'in_transit', 'Left Phuentsholing'], [$event->track, $event->status, $event->note]);
    }

    // ---- reading it back

    public function test_stage_on_a_track_primary_status_and_a_track_that_is_off(): void
    {
        $both = $this->both();
        $this->assertSame('declaration_submitted', $both->stageOn('customs'));
        $this->assertSame('order_placed', $both->stageOn('transport'));
        $this->assertSame('declaration_submitted', $both->primaryStatus());

        $customsOnly = $this->shipment();
        $this->assertNull($customsOnly->stageOn('transport'));

        $transportOnly = $this->transportOnly();
        $this->assertNull($transportOnly->stageOn('customs'));
        $this->assertSame('order_placed', $transportOnly->primaryStatus());
    }

    public function test_a_switched_off_track_keeps_its_data_but_is_ignored(): void
    {
        $s = $this->both();
        $s->update(['transport_status' => 'in_transit']);
        $s->update(['transport_enabled' => false]);
        $s = $s->fresh();
        $this->assertSame('in_transit', $s->transport_status, 'the history is kept');
        $this->assertNull($s->stageOn('transport'), 'but it is no longer shown');
        $this->assertSame(['customs'], $s->tracks());
    }

    public function test_finished_means_every_track_that_is_on_is_at_its_last_stage(): void
    {
        $customs = $this->shipment();
        $this->assertFalse($customs->isFinished());
        $customs->update(['status' => 'released']);
        $this->assertTrue($customs->fresh()->isFinished());

        $both = $this->both(['status' => 'released']);
        $this->assertFalse($both->fresh()->isFinished(), 'transport is still at Order Placed');
        $both->update(['transport_status' => 'delivered']);
        $this->assertTrue($both->fresh()->isFinished());

        $this->assertFalse($this->transportOnly()->isFinished());
        $this->assertTrue($this->transportOnly(['transport_status' => 'delivered'])->isFinished());
    }

    public function test_the_in_progress_scope_counts_open_shipments_on_any_track(): void
    {
        $this->shipment(null, ['status' => 'released']);                                   // done
        $open = $this->shipment();                                                          // customs open
        $this->both(['status' => 'released']);                                              // customs done, transport open
        $this->both(['status' => 'released', 'transport_status' => 'delivered']);           // both done
        $this->transportOnly(['transport_status' => 'delivered']);                          // done
        $this->transportOnly(['transport_status' => 'in_transit']);                         // transport open
        $off = $this->shipment(null, ['status' => 'released']);                             // customs done, transport off
        $off->update(['transport_enabled' => false, 'transport_status' => 'in_transit']);   // an ignored, unfinished transport stage

        $this->assertSame(3, Shipment::inProgress()->count());
        $this->assertTrue(Shipment::inProgress()->whereKey($open->id)->exists());
        $this->assertFalse(Shipment::inProgress()->whereKey($off->id)->exists(), 'a switched-off track never keeps a shipment open');
    }

    // ---- the database defaults an old row relies on

    public function test_rows_written_before_this_change_read_as_customs_on_transport_off(): void
    {
        $client = $this->client();
        $id = DB::table('shipments')->insertGetId([
            'tracking_number' => 'TD-2610-OLDONE', 'client_id' => $client->id, 'direction' => 'import', 'mode' => 'road', 'goods' => 'Old goods',
            'status' => 'pending_risk', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('shipment_events')->insert(['shipment_id' => $id, 'status' => 'pending_risk', 'created_at' => now()]);

        $s = Shipment::findOrFail($id);
        $this->assertTrue($s->customs_enabled);
        $this->assertFalse($s->transport_enabled);
        $this->assertSame('customs', $s->events->first()->track);
        $this->assertSame('pending_risk', $s->primaryStatus());
    }

    // ---- nothing breaks for a shipment with no customs stage

    public function test_the_staff_and_public_pages_cope_with_a_transport_only_shipment(): void
    {
        $client = $this->client(['company' => 'Druk Motors', 'phone' => '+97517975757']);
        $s = $this->shipment($client, ['customs_enabled' => false, 'transport_enabled' => true]);
        $this->actingAs($this->staff());

        $this->get('/staff')->assertOk()->assertSee($s->tracking_number)->assertSee('Order Placed');
        $this->get("/staff/shipments/{$s->tracking_number}")->assertOk()->assertSee('Order Placed');
        $this->get("/staff/clients/{$client->id}")->assertOk()->assertSee($s->tracking_number)->assertSee('Order Placed');
        $this->get('/staff/export/shipments.csv')->assertOk();
        $csv = $this->get('/staff/export/shipments.csv')->streamedContent();
        $this->assertStringContainsString('Order Placed', $csv);
        $this->assertMatchesRegularExpression('#In progress</dt>\s*<dd[^>]*>1</dd>#', $this->get('/staff')->getContent());

        auth()->logout();
        $this->post('/track', ['tracking' => $s->tracking_number], ['X-Enhance' => '1'])->assertOk()->assertSee($s->tracking_number)->assertSee('Order Placed');
        $this->get('/track?n='.$s->tracking_number)->assertOk()->assertSee('Order Placed');
    }

    public function test_switching_transport_on_later_starts_it_at_order_placed_and_logs_it(): void
    {
        $s = $this->shipment();
        $this->assertCount(1, $s->events);

        $s->update(['transport_enabled' => true]);
        $s = $s->fresh();
        $this->assertSame('order_placed', $s->transport_status);
        $this->assertContains(['transport', 'order_placed'], $s->events->map(fn ($e) => [$e->track, $e->status])->all());
        $this->assertCount(2, $s->events);
    }

    public function test_switching_customs_on_for_a_transport_only_shipment_starts_it_at_the_first_customs_stage(): void
    {
        $s = $this->transportOnly();
        $s->update(['customs_enabled' => true]);
        $s = $s->fresh();
        $this->assertSame('declaration_submitted', $s->status);
        $this->assertContains(['customs', 'declaration_submitted'], $s->events->map(fn ($e) => [$e->track, $e->status])->all());
    }

    public function test_switching_a_track_off_and_on_again_resumes_where_it_was_without_a_new_entry(): void
    {
        $s = $this->both();
        $s->update(['transport_status' => 'in_transit']);
        $count = $s->events()->count();

        $s->update(['transport_enabled' => false]);
        $s->update(['transport_enabled' => true]);
        $s = $s->fresh();
        $this->assertSame('in_transit', $s->transport_status);
        $this->assertSame($count, $s->events()->count(), 'the same stage, so nothing new on the timeline');
    }

    public function test_a_shipment_cannot_have_both_tracks_off(): void
    {
        $s = $this->both();
        $this->expectException(\InvalidArgumentException::class);
        $s->update(['customs_enabled' => false, 'transport_enabled' => false]);
    }

    public function test_a_shipment_cannot_be_created_with_both_tracks_off(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->shipment(null, ['customs_enabled' => false, 'transport_enabled' => false]);
    }
}

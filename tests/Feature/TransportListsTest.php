<?php

namespace Tests\Feature;

use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class TransportListsTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    private function both(array $attrs = []): Shipment
    {
        return $this->shipment(null, $attrs + ['transport_enabled' => true]);
    }

    private function transportOnly(array $attrs = []): Shipment
    {
        return $this->shipment(null, $attrs + ['customs_enabled' => false, 'transport_enabled' => true]);
    }

    private function chip(string $html, string $label): ?array
    {
        return preg_match('#<a href="([^"]*)"([^>]*)>'.preg_quote($label, '#').' <span[^>]*>(\d+)</span></a>#', html_entity_decode($html), $m) ? ['href' => $m[1], 'current' => str_contains($m[2], 'aria-current'), 'count' => (int) $m[3]] : null;
    }

    // ---------- the chips

    public function test_with_no_transport_in_use_the_page_looks_exactly_as_before(): void
    {
        $this->shipment();
        $html = $this->actingAs($this->staff())->get('/staff')->assertOk()->getContent();
        $this->assertNull($this->chip($html, 'In Transit'), 'no transport chips');
        $this->assertStringNotContainsString('Customs clearance</p>', $html, 'no row labels either');
        $this->assertSame(1, $this->chip($html, 'All')['count']);
        $this->assertSame(1, $this->chip($html, 'Declaration Submitted')['count']);
    }

    public function test_once_transport_is_in_use_a_labelled_transport_row_of_chips_appears(): void
    {
        $this->both()->update(['transport_status' => 'in_transit']);
        $this->both();
        $this->shipment();
        $html = $this->actingAs($this->staff())->get('/staff')->assertOk()->assertSee('Customs clearance')->assertSee('Transport')->getContent();

        $this->assertSame(3, $this->chip($html, 'All')['count']);
        $this->assertSame(3, $this->chip($html, 'Declaration Submitted')['count'], 'all three are at customs stage 1');
        $this->assertSame(1, $this->chip($html, 'In Transit')['count']);
        $this->assertSame(1, $this->chip($html, 'Order Placed')['count']);
        $this->assertSame(0, $this->chip($html, 'Delivered')['count']);
        foreach (['Order Placed', 'Processing', 'Ready for Pickup', 'In Transit', 'Out for Delivery', 'Delivered'] as $label) {
            $this->assertNotNull($this->chip($html, $label), $label);
        }
    }

    public function test_a_transport_chip_filters_to_shipments_at_that_transport_stage(): void
    {
        $a = $this->both(['goods' => 'On the road']);
        $a->update(['transport_status' => 'in_transit']);
        $this->both(['goods' => 'Just ordered']);
        $this->shipment(null, ['goods' => 'Customs only']);
        $this->actingAs($this->staff());

        $this->get('/staff?status=in_transit')->assertOk()->assertSee('On the road')->assertDontSee('Just ordered')->assertDontSee('Customs only')->assertSee('1 shipment matches');
        $this->get('/staff?status=order_placed')->assertSee('Just ordered')->assertDontSee('On the road');
        $html = $this->get('/staff?status=in_transit')->getContent();
        $this->assertTrue($this->chip($html, 'In Transit')['current'], 'the chosen chip is marked');
        $this->assertFalse($this->chip($html, 'All')['current']);
        $this->assertStringContainsString('<input type="hidden" name="status" value="in_transit">', $html, 'the search keeps the stage');
    }

    public function test_the_chosen_transport_stage_keeps_its_row_even_if_nothing_is_in_use_any_more(): void
    {
        $this->shipment();
        $html = $this->actingAs($this->staff())->get('/staff?status=in_transit')->assertOk()->getContent();
        $this->assertNotNull($this->chip($html, 'In Transit'), 'a bookmarked transport filter still shows its chip');
        $this->assertTrue($this->chip($html, 'In Transit')['current']);
    }

    public function test_customs_chips_ignore_a_track_that_is_switched_off(): void
    {
        $s = $this->both(['status' => 'pending_risk']);
        $s->update(['customs_enabled' => false]);                       // customs off: its stage is remembered, but ignored
        $this->actingAs($this->staff());

        $this->get('/staff?status=pending_risk')->assertSee('No shipments match')->assertDontSee($s->tracking_number);
        $html = $this->get('/staff')->getContent();
        $this->assertSame(0, $this->chip($html, 'Pending Risk')['count']);
        $this->assertSame(1, $this->chip($html, 'Order Placed')['count']);
    }

    public function test_the_counts_follow_the_search_and_the_chip_links_keep_it(): void
    {
        $tashi = $this->client(['full_name' => 'Tashi Wangmo']);
        $pema = $this->client(['full_name' => 'Pema Choden']);
        $this->shipment($tashi, ['transport_enabled' => true]);
        $this->shipment($pema, ['transport_enabled' => true, 'transport_status' => 'in_transit']);
        $html = $this->actingAs($this->staff())->get('/staff?q=tashi')->getContent();

        $this->assertSame(1, $this->chip($html, 'All')['count']);
        $this->assertSame(1, $this->chip($html, 'Order Placed')['count']);
        $this->assertSame(0, $this->chip($html, 'In Transit')['count'], 'Pema is filtered out by the search');
        $this->assertStringContainsString('/staff?q=tashi&status=in_transit', $this->chip($html, 'In Transit')['href']);
    }

    public function test_a_shipment_on_transport_only_is_found_and_counted(): void
    {
        $s = $this->transportOnly(['goods' => 'Transport only goods']);
        $s->update(['transport_status' => 'out_for_delivery']);
        $this->actingAs($this->staff());

        $html = $this->get('/staff')->assertOk()->assertSee('Transport only goods')->getContent();
        $this->assertSame(1, $this->chip($html, 'All')['count']);
        $this->assertSame(1, $this->chip($html, 'Out for Delivery')['count']);
        $this->assertSame(0, $this->chip($html, 'Declaration Submitted')['count']);
        $this->get('/staff?status=out_for_delivery')->assertSee('Transport only goods');
    }

    // ---------- the stage column

    public function test_the_stage_column_shows_a_badge_per_track_and_labels_them_when_there_are_two(): void
    {
        $both = $this->both(['goods' => 'Both goods']);
        $both->update(['transport_status' => 'in_transit']);
        $one = $this->shipment(null, ['goods' => 'One goods']);
        $html = $this->actingAs($this->staff())->get('/staff')->getContent();

        $rowBoth = substr($html, strpos($html, 'Both goods'), 1500);
        $this->assertStringContainsString('>Customs</span>', $rowBoth);
        $this->assertStringContainsString('>Transport</span>', $rowBoth);
        $this->assertStringContainsString('Declaration Submitted', $rowBoth);
        $this->assertStringContainsString('In Transit', $rowBoth);

        $rowOne = substr($html, strpos($html, 'One goods'), 700);
        $this->assertStringNotContainsString('>Customs</span>', $rowOne, 'a single track needs no label');
    }

    public function test_the_clients_request_list_uses_the_same_badges(): void
    {
        $client = $this->client();
        $s = $this->shipment($client, ['transport_enabled' => true]);
        $s->update(['transport_status' => 'ready_for_pickup']);
        $this->actingAs($this->staff())->get("/staff/clients/{$client->id}")->assertOk()->assertSee('Ready for Pickup')->assertSee('Declaration Submitted');
    }

    // ---------- the numbers

    public function test_in_progress_and_your_number_count_open_work_on_either_track(): void
    {
        $me = $this->staff();
        $this->shipment(null, ['assigned_to' => $me->id, 'status' => 'released']);                                         // done
        $this->shipment(null, ['assigned_to' => $me->id, 'status' => 'released', 'transport_enabled' => true]);            // customs done, transport open: still open
        $this->transportOnly(['assigned_to' => $me->id, 'transport_status' => 'delivered']);                               // done
        $html = $this->actingAs($me)->get('/staff')->getContent();
        $this->assertMatchesRegularExpression('#In progress</dt>\s*<dd[^>]*>1</dd>#', $html);
        $this->assertMatchesRegularExpression('#Yours</span><span[^>]*>Looked after by you</span></a></dt>\s*<dd[^>]*>1</dd>#', $html);
    }

    // ---------- the CSV

    public function test_the_csv_has_transport_columns_and_follows_a_transport_stage_filter(): void
    {
        $s = $this->both(['goods' => 'Carried goods', 'carrier' => 'Druk Carriers', 'vehicle_number' => 'BP-1-A1234', 'pickup_place' => 'Phuentsholing', 'destination' => 'Gelephu', 'expected_delivery' => '2026-11-02', 'driver_name' => 'Secret Driver', 'driver_phone' => '+97517999888']);
        $s->update(['transport_status' => 'delivered', 'delivered_to' => 'Tashi', 'delivered_at' => '2026-10-28 08:30:00']);
        $this->shipment(null, ['goods' => 'Customs only goods']);

        $body = $this->actingAs($this->staff())->get('/staff/export/shipments.csv?status=delivered')->streamedContent();
        $rows = array_map(fn ($l) => str_getcsv($l, ',', '"', ''), array_values(array_filter(preg_split('/\r?\n/', substr($body, 3)))));
        $this->assertCount(2, $rows, 'only the delivered shipment');
        $h = array_flip($rows[0]);
        $r = $rows[1];
        $this->assertSame('Carried goods', $r[$h['Goods']]);
        $this->assertSame(['Declaration Submitted', 'Delivered', '2026-11-02', 'Tashi', '2026-10-28 14:30'], [$r[$h['Customs stage']], $r[$h['Transport stage']], $r[$h['Expected delivery']], $r[$h['Delivered to']], $r[$h['Delivered at']]]);
        $this->assertSame(['Druk Carriers', 'BP-1-A1234', 'Phuentsholing', 'Gelephu'], [$r[$h['Carrier']], $r[$h['Vehicle number']], $r[$h['Pickup place']], $r[$h['Destination']]]);
        $this->assertStringNotContainsString('Secret Driver', $body, 'the driver stays out of the file');
        $this->assertStringNotContainsString('17999888', $body);
    }

    public function test_a_customs_only_row_has_empty_transport_columns(): void
    {
        $this->shipment(null, ['carrier' => 'Left over', 'destination' => 'Left over too']);
        $body = $this->actingAs($this->staff())->get('/staff/export/shipments.csv')->streamedContent();
        $this->assertStringNotContainsString('Left over', $body, 'details of a track that is off are not exported');
    }
}

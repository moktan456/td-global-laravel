<?php

namespace Tests\Feature;

use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class AssignShipmentsTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    private function person(string $name, array $attrs = [])
    {
        return $this->staff($attrs + ['name' => $name]);
    }

    public function test_a_new_request_starts_with_whoever_creates_it_and_the_form_offers_the_team(): void
    {
        $me = $this->person('Creator One');
        $other = $this->person('Pema Dorji');
        $off = $this->person('Switched Off', ['active' => false]);
        $client = $this->client();

        $html = $this->actingAs($me)->get('/staff/shipments/new')->assertOk()->assertSee('Looked after by')->assertSee('Nobody yet')->getContent();
        $this->assertMatchesRegularExpression('#<option value="'.$me->id.'"[^>]*selected[^>]*>Creator One \(you\)#', $html);
        $this->assertStringContainsString('Pema Dorji', $html);
        $this->assertStringNotContainsString('Switched Off', $html, 'people who cannot sign in are not offered');

        $this->actingAs($me)->post('/staff/shipments', ['client_id' => $client->id, 'direction' => 'import', 'mode' => 'road', 'goods' => 'Rice', 'details' => '', 'assigned_to' => (string) $me->id], ['X-Enhance' => '1'])->assertNoContent();
        $this->assertSame($me->id, Shipment::firstOrFail()->assigned_to);
    }

    public function test_a_request_can_be_created_for_someone_else_or_for_nobody_and_bad_ids_are_ignored(): void
    {
        $me = $this->person('Creator One');
        $other = $this->person('Pema Dorji');
        $off = $this->person('Switched Off', ['active' => false]);
        $client = $this->client();
        $post = fn ($who) => $this->actingAs($me)->post('/staff/shipments', ['client_id' => $client->id, 'direction' => 'import', 'mode' => 'road', 'goods' => 'Rice', 'details' => '', 'assigned_to' => $who], ['X-Enhance' => '1'])->assertNoContent();

        $post((string) $other->id);
        $post('');
        $post((string) $off->id);
        $post('99999');
        $this->assertSame([$other->id, null, null, null], Shipment::orderBy('id')->pluck('assigned_to')->all());
    }

    public function test_staff_can_assign_reassign_and_clear_a_shipment_from_its_page(): void
    {
        $me = $this->person('Creator One');
        $pema = $this->person('Pema Dorji');
        $s = $this->shipment();

        $this->actingAs($me)->get("/staff/shipments/{$s->tracking_number}")->assertOk()->assertSee('Looked after by');

        $this->post("/staff/shipments/{$s->tracking_number}/assign", ['assigned_to' => (string) $pema->id], ['X-Enhance' => '1'])->assertOk()->assertSee('Assigned to Pema Dorji.');
        $this->assertSame($pema->id, $s->fresh()->assigned_to);

        $this->post("/staff/shipments/{$s->tracking_number}/assign", ['assigned_to' => (string) $me->id], ['X-Enhance' => '1'])->assertSee('Assigned to Creator One.');
        $this->post("/staff/shipments/{$s->tracking_number}/assign", ['assigned_to' => ''], ['X-Enhance' => '1'])->assertSee('Now unassigned.');
        $this->assertNull($s->fresh()->assigned_to);
    }

    public function test_assigning_does_not_touch_the_timeline_or_the_stage(): void
    {
        $pema = $this->person('Pema Dorji');
        $s = $this->shipment();
        $before = $s->events()->count();
        $this->actingAs($this->staff())->post("/staff/shipments/{$s->tracking_number}/assign", ['assigned_to' => (string) $pema->id], ['X-Enhance' => '1'])->assertOk();
        $this->assertSame($before, $s->events()->count());
        $this->assertSame('declaration_submitted', $s->fresh()->status);
    }

    public function test_a_bad_choice_is_refused_and_an_unknown_shipment_is_a_404(): void
    {
        $off = $this->person('Switched Off', ['active' => false]);
        $s = $this->shipment();
        $this->actingAs($this->staff());
        $this->post("/staff/shipments/{$s->tracking_number}/assign", ['assigned_to' => (string) $off->id], ['X-Enhance' => '1'])->assertSee('Choose someone from the list.');
        $this->post("/staff/shipments/{$s->tracking_number}/assign", ['assigned_to' => '99999'], ['X-Enhance' => '1'])->assertSee('Choose someone from the list.');
        $this->assertNull($s->fresh()->assigned_to);
        $this->post('/staff/shipments/TD-0000-ZZZZZZ/assign', ['assigned_to' => ''], ['X-Enhance' => '1'])->assertNotFound();
    }

    public function test_assigning_needs_a_staff_sign_in(): void
    {
        $s = $this->shipment();
        $this->post("/staff/shipments/{$s->tracking_number}/assign", ['assigned_to' => ''])->assertRedirect('/staff/login');
    }

    public function test_someone_since_switched_off_still_shows_as_the_assignee(): void
    {
        $gone = $this->person('Leaver Lhamo');
        $s = $this->shipment(null, ['assigned_to' => $gone->id]);
        $gone->forceFill(['active' => false])->save();
        $this->actingAs($this->staff())->get("/staff/shipments/{$s->tracking_number}")->assertOk()->assertSee('Leaver Lhamo (switched off)');
    }

    public function test_the_list_shows_who_looks_after_each_shipment_and_filters_by_it(): void
    {
        $me = $this->person('Creator One');
        $pema = $this->person('Pema Dorji');
        $mine = $this->shipment(null, ['goods' => 'Mine goods', 'assigned_to' => $me->id]);
        $hers = $this->shipment(null, ['goods' => 'Hers goods', 'assigned_to' => $pema->id]);
        $nobody = $this->shipment(null, ['goods' => 'Nobody goods']);

        $this->actingAs($me)->get('/staff')->assertOk()->assertSee('Looked after by')->assertSee('Mine goods')->assertSee('Hers goods')->assertSee('Nobody goods')->assertSee('Nobody yet');
        $this->get('/staff?assigned=me')->assertSee('Mine goods')->assertDontSee('Hers goods')->assertDontSee('Nobody goods');
        $this->get('/staff?assigned=none')->assertSee('Nobody goods')->assertDontSee('Mine goods')->assertDontSee('Hers goods');
        $this->get('/staff?assigned='.$pema->id)->assertSee('Hers goods')->assertDontSee('Mine goods')->assertDontSee('Nobody goods');
        $this->get('/staff?assigned[]=x&assigned=abc')->assertOk()->assertSee('Mine goods')->assertSee('Hers goods');
    }

    public function test_the_stage_counts_and_chip_links_follow_the_assigned_filter(): void
    {
        $me = $this->person('Creator One');
        $this->shipment(null, ['assigned_to' => $me->id]);
        $this->shipment(null, ['assigned_to' => $me->id, 'status' => 'released']);
        $this->shipment();
        $html = html_entity_decode($this->actingAs($me)->get('/staff?assigned=me')->getContent());

        $this->assertMatchesRegularExpression('#<a href="/staff\?assigned=me"[^>]*>All <span[^>]*>2</span>#', $html);
        $this->assertStringContainsString('/staff?status=released&assigned=me', $html);
        $this->assertStringContainsString('<input type="hidden" name="status"', $this->actingAs($me)->get('/staff?status=released&assigned=me')->getContent());
    }

    public function test_the_looked_after_by_you_number_counts_open_shipments_only(): void
    {
        $me = $this->person('Creator One');
        $this->shipment(null, ['assigned_to' => $me->id]);
        $this->shipment(null, ['assigned_to' => $me->id, 'status' => 'pending_risk']);
        $this->shipment(null, ['assigned_to' => $me->id, 'status' => 'released']);
        $this->shipment(null, ['assigned_to' => $this->person('Pema Dorji')->id]);
        $html = $this->actingAs($me)->get('/staff')->getContent();
        $this->assertMatchesRegularExpression('#Looked after by you</span></a></dt>\s*<dd[^>]*>2</dd>#', $html);
    }

    public function test_the_csv_has_an_assigned_to_column_and_follows_the_filter(): void
    {
        $me = $this->person('Creator One');
        $this->shipment(null, ['goods' => 'Mine goods', 'assigned_to' => $me->id]);
        $this->shipment(null, ['goods' => 'Nobody goods']);

        $rows = array_map(fn ($l) => str_getcsv($l, ',', '"', ''), array_values(array_filter(preg_split('/\r?\n/', substr($this->actingAs($me)->get('/staff/export/shipments.csv')->streamedContent(), 3)))));
        $this->assertSame('Assigned to', $rows[0][13]);
        $this->assertSame(['Nobody goods' => '', 'Mine goods' => 'Creator One'], collect(array_slice($rows, 1))->pluck(13, 7)->all());

        $mine = array_map(fn ($l) => str_getcsv($l, ',', '"', ''), array_values(array_filter(preg_split('/\r?\n/', substr($this->actingAs($me)->get('/staff/export/shipments.csv?assigned=me')->streamedContent(), 3)))));
        $this->assertCount(2, $mine, 'the heading and one shipment');
        $this->assertSame('Mine goods', $mine[1][7]);
    }
}

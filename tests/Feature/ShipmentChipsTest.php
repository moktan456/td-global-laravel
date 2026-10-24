<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class ShipmentChipsTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    private function chip(string $html, string $label): ?string
    {
        // The chip's text is "<label> <count>" inside a link in the stage filter.
        return preg_match('#<a href="([^"]*)"[^>]*>'.preg_quote($label, '#').' <span[^>]*>(\d+)</span></a>#', $html, $m) ? "{$m[1]}|{$m[2]}" : null;
    }

    public function test_each_stage_chip_shows_how_many_shipments_are_there(): void
    {
        $client = $this->client();
        $this->shipment($client);
        $this->shipment($client);
        $this->shipment($client, ['status' => 'pending_payment']);
        $this->shipment($client, ['status' => 'released']);
        $html = $this->actingAs($this->staff())->get('/staff')->assertOk()->getContent();

        $this->assertSame(4, (int) explode('|', $this->chip($html, 'All'))[1]);
        $this->assertSame(2, (int) explode('|', $this->chip($html, 'Declaration Submitted'))[1]);
        $this->assertSame(1, (int) explode('|', $this->chip($html, 'Pending Payment'))[1]);
        $this->assertSame(1, (int) explode('|', $this->chip($html, 'Released'))[1]);
        $this->assertSame(0, (int) explode('|', $this->chip($html, 'Pending Risk'))[1]);
    }

    public function test_counts_follow_the_search_and_the_chip_links_keep_it(): void
    {
        $tashi = $this->client(['full_name' => 'Tashi Wangmo']);
        $pema = $this->client(['full_name' => 'Pema Choden']);
        $this->shipment($tashi);
        $this->shipment($tashi, ['status' => 'released']);
        $this->shipment($pema);
        $html = html_entity_decode($this->actingAs($this->staff())->get('/staff?q=tashi')->getContent());

        $this->assertSame(2, (int) explode('|', $this->chip($html, 'All'))[1], 'only Tashi\'s shipments are counted');
        $this->assertSame(1, (int) explode('|', $this->chip($html, 'Declaration Submitted'))[1]);
        $this->assertStringContainsString('/staff?q=tashi&status=released', explode('|', $this->chip($html, 'Released'))[0]);
        $this->assertSame('/staff?q=tashi', explode('|', $this->chip($html, 'All'))[0]);
    }

    public function test_the_chosen_stage_is_marked_and_filters_the_list_while_the_other_counts_stay(): void
    {
        $client = $this->client();
        $open = $this->shipment($client, ['goods' => 'Open goods']);
        $done = $this->shipment($client, ['status' => 'released', 'goods' => 'Done goods']);
        $html = $this->actingAs($this->staff())->get('/staff?status=released')->assertOk()->assertSee('Done goods')->assertDontSee('Open goods')->getContent();

        $this->assertMatchesRegularExpression('#aria-current="page"[^>]*>Released <span#', $html);
        $this->assertSame(1, (int) explode('|', $this->chip($html, 'Declaration Submitted'))[1], 'the other stages keep their counts');
    }

    public function test_the_search_box_keeps_the_chosen_stage(): void
    {
        $html = $this->actingAs($this->staff())->get('/staff?status=released')->getContent();
        $this->assertStringContainsString('<input type="hidden" name="status" value="released">', $html);
    }

    public function test_in_progress_leaves_out_released_and_updated_today_counts_todays_changes(): void
    {
        $client = $this->client();
        $this->shipment($client);
        $this->shipment($client, ['status' => 'pending_risk']);
        $old = $this->shipment($client, ['status' => 'released']);
        $stale = $this->shipment($client);
        DB::table('shipments')->where('id', $stale->id)->update(['updated_at' => now()->subDays(3)]);

        $html = $this->actingAs($this->staff())->get('/staff')->getContent();
        $this->assertMatchesRegularExpression('#In progress</dt>\s*<dd[^>]*>3</dd>#', $html);   // two fresh + the stale one; not the released
        $this->assertMatchesRegularExpression('#Updated today</dt>\s*<dd[^>]*>3</dd>#', $html);  // three were touched just now
    }

    public function test_the_list_has_a_card_layout_for_phones(): void
    {
        $this->shipment();
        $html = $this->actingAs($this->staff())->get('/staff')->getContent();
        $this->assertStringContainsString('md:grid-cols-[11rem_', $html, 'a grid on wide screens');
        $this->assertStringNotContainsString('min-w-[720px]', $html, 'no table forcing a sideways scroll');
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class ShipmentSearchTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_the_list_is_paged_fifteen_at_a_time_most_recently_updated_first(): void
    {
        $client = $this->client();
        for ($i = 1; $i <= 40; $i++) {
            $this->shipment($client, ['goods' => sprintf('Goods %02d', $i)]);
        }
        $this->actingAs($this->staff());

        $this->get('/staff')->assertOk()->assertSee('Showing 1–15 of 40')->assertSee('Page 1 of 3')->assertSee('Goods 40')->assertSee('Goods 26')->assertDontSee('Goods 25');
        $this->get('/staff?page=3')->assertSee('Showing 31–40 of 40')->assertSee('Goods 10')->assertDontSee('Goods 11');
    }

    public function test_a_page_that_does_not_exist_goes_to_the_last_one(): void
    {
        $this->shipment();
        $this->actingAs($this->staff());
        $this->get('/staff?page=9')->assertRedirect('/staff?page=1');
    }

    public function test_search_finds_shipments_by_tracking_number_with_or_without_dashes_and_in_any_case(): void
    {
        $a = $this->shipment();
        $b = $this->shipment();
        $this->actingAs($this->staff());

        $this->get('/staff?q='.urlencode(strtolower($a->tracking_number)))->assertSee($a->tracking_number)->assertDontSee($b->tracking_number);
        $this->get('/staff?q='.urlencode(str_replace('-', '', $a->tracking_number)))->assertSee($a->tracking_number)->assertDontSee($b->tracking_number);
        $this->get('/staff?q='.urlencode(substr($a->tracking_number, -6)))->assertSee($a->tracking_number);
    }

    public function test_search_finds_shipments_by_client_name_company_phone_and_goods(): void
    {
        $tashi = $this->client(['full_name' => 'Tashi Wangmo', 'company' => 'Druk Motors', 'email' => 'tashi@druk.bt', 'phone' => '+97517975757']);
        $pema = $this->client(['full_name' => 'Pema Choden', 'company' => 'Gelephu Foods', 'phone' => '+97577123456']);
        $a = $this->shipment($tashi, ['goods' => '2 pallets of auto parts']);
        $b = $this->shipment($pema, ['goods' => 'Rice and cooking oil']);
        $this->actingAs($this->staff());

        $this->get('/staff?q=tashi')->assertSee($a->tracking_number)->assertDontSee($b->tracking_number)->assertSee('1 shipment matches');
        $this->get('/staff?q=druk')->assertSee($a->tracking_number)->assertDontSee($b->tracking_number);
        $this->get('/staff?q=tashi@druk')->assertSee($a->tracking_number)->assertDontSee($b->tracking_number);
        $this->get('/staff?q='.urlencode('+975 17 975757'))->assertSee($a->tracking_number)->assertDontSee($b->tracking_number);
        $this->get('/staff?q=rice')->assertSee($b->tracking_number)->assertDontSee($a->tracking_number);
        $this->get('/staff?q='.urlencode('pema oil'))->assertSee($b->tracking_number)->assertDontSee($a->tracking_number);   // every word must match
        $this->get('/staff?q='.urlencode('tashi oil'))->assertSee('No shipments match');
    }

    public function test_search_and_stage_filter_work_together_and_the_page_links_keep_them(): void
    {
        $client = $this->client(['full_name' => 'Tashi Wangmo']);
        for ($i = 0; $i < 17; $i++) {
            $this->shipment($client, ['status' => 'released']);
        }
        $other = $this->shipment($this->client(['full_name' => 'Someone Else']), ['status' => 'released']);
        $this->actingAs($this->staff());

        $this->get('/staff?status=released&q=tashi')->assertSee('17 shipments match')->assertDontSee($other->tracking_number);
        $this->get('/staff?status=pending_risk&q=tashi')->assertSee('No shipments match');
        $html = html_entity_decode($this->get('/staff?status=released&q=tashi')->getContent());
        $this->assertMatchesRegularExpression('#href="[^"]*q=tashi[^"]*page=2#', $html);
    }

    public function test_percent_is_searched_literally_and_junk_in_the_query_string_is_ignored(): void
    {
        $this->shipment(null, ['goods' => '100% cotton']);
        $this->shipment(null, ['goods' => 'plain goods']);
        $this->actingAs($this->staff());

        $this->get('/staff?q='.urlencode('%'))->assertSee('100% cotton')->assertDontSee('plain goods');
        $this->get('/staff?q[]=x&status[]=y&page=abc')->assertOk();
    }
}

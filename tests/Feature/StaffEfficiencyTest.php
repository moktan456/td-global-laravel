<?php

namespace Tests\Feature;

use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class StaffEfficiencyTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    private function ship(string $goods, string $status = 'declaration_submitted'): Shipment
    {
        return Shipment::create(['client_id' => $this->client()->id, 'direction' => 'import', 'mode' => 'road', 'goods' => $goods, 'status' => $status]);
    }

    public function test_find_a_shipment_opens_an_exact_tracking_number_in_any_spelling(): void
    {
        $me = $this->staff();
        $s = $this->ship('Rice');
        $plain = strtolower(str_replace('-', '', $s->tracking_number));

        $this->actingAs($me)->get('/staff/go?n='.$plain)->assertRedirect("/staff/shipments/{$s->tracking_number}");
        $this->actingAs($me)->get('/staff/go?n='.urlencode(' '.$s->tracking_number.' '))->assertRedirect("/staff/shipments/{$s->tracking_number}");
        $this->actingAs($me)->get('/staff/go?n='.substr($plain, -6))->assertRedirect("/staff/shipments/{$s->tracking_number}");
    }

    public function test_find_a_shipment_falls_back_to_a_search_and_needs_sign_in(): void
    {
        $this->actingAs($this->staff())->get('/staff/go?n=rice')->assertRedirect('/staff?q=rice');
        $this->actingAs($this->staff())->get('/staff/go')->assertRedirect('/staff');
        auth()->logout();
        $this->get('/staff/go?n=rice')->assertRedirect();
    }

    public function test_the_list_can_be_sorted_and_unknown_sorts_are_ignored(): void
    {
        $me = $this->staff();
        $a = $this->ship('Alpha goods', 'pending_payment');
        $b = $this->ship('Bravo goods', 'declaration_submitted');
        $order = fn (string $q) => (function ($html) use ($a, $b) {
            return strpos($html, $a->tracking_number) < strpos($html, $b->tracking_number) ? 'a-first' : 'b-first';
        })($this->actingAs($me)->get('/staff'.$q)->assertOk()->getContent());

        $this->assertSame('b-first', $order('?sort=stage'), 'earlier customs stage first');
        $this->assertSame('b-first', $order('?sort=nonsense'), 'unknown sort means newest first');
        $this->actingAs($me)->get('/staff?sort=client')->assertOk();
        $this->actingAs($me)->get('/staff?sort=oldest')->assertOk()->assertSee('Longest since updated');
    }

    public function test_the_list_is_a_real_table_for_screen_readers_and_filters_fold_on_phones(): void
    {
        $this->ship('Rice');
        $this->actingAs($this->staff())->get('/staff')->assertOk()
            ->assertSee('role="table"', false)->assertSee('role="columnheader"', false)->assertSee('role="cell"', false)
            ->assertSee('data-fold-on-phone', false)->assertSee('data-find', false);
    }

    public function test_switching_a_person_off_asks_first_but_switching_back_on_does_not(): void
    {
        $admin = $this->staff(['role' => 'admin']);
        $other = $this->staff(['name' => 'Pema Dorji']);
        $this->actingAs($admin)->get('/staff/team')->assertOk()->assertSee('data-confirm="Switch off Pema Dorji?', false);
        $other->forceFill(['active' => false])->save();
        $this->actingAs($admin)->get('/staff/team')->assertOk()->assertDontSee('data-confirm="Switch off Pema', false);
    }

    public function test_the_list_can_change_a_stage_in_place_and_comes_back_with_a_message(): void
    {
        $me = $this->staff();
        $s = $this->ship('Rice');

        $this->actingAs($me)->get('/staff')->assertOk()->assertSee('Change stage')->assertSee('Compact rows');
        $this->actingAs($me)->from('/staff?sort=client')->post("/staff/shipments/{$s->tracking_number}/status", ['from' => 'list', 'status' => 'pending_payment'])
            ->assertRedirect('/staff?sort=client')->assertSessionHas('quick.ok', true);
        $this->assertSame('pending_payment', $s->fresh()->status);

        $this->actingAs($me)->from('/staff')->post("/staff/shipments/{$s->tracking_number}/status", ['from' => 'list', 'status' => 'nonsense'])
            ->assertRedirect('/staff')->assertSessionHas('quick.ok', false);
        $this->assertSame('pending_payment', $s->fresh()->status);
    }

    public function test_a_shipment_page_has_a_phone_jump_bar_and_the_list_has_none(): void
    {
        $s = $this->ship('Rice');
        $this->actingAs($this->staff())->get("/staff/shipments/{$s->tracking_number}")->assertOk()->assertSee('aria-label="Jump to a section"', false)->assertSee('#timeline-heading', false);
        $this->actingAs($this->staff())->get('/staff')->assertDontSee('Jump to a section');
    }

    public function test_staff_pages_can_go_dark_and_the_public_site_never_does(): void
    {
        $this->actingAs($this->staff())->get('/staff')->assertOk()
            ->assertSee("localStorage.getItem('staff-theme')", false)->assertSee('data-theme-toggle', false);
        auth()->logout();
        $this->get('/staff/login')->assertOk()->assertSee('staff-theme', false);
        $this->get('/')->assertOk()->assertDontSee('staff-theme', false)->assertDontSee('data-theme', false);
    }
}

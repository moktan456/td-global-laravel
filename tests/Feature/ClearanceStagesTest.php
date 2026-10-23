<?php

namespace Tests\Feature;

use App\Support\Statuses;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesStaff;
use Tests\TestCase;

class ClearanceStagesTest extends TestCase
{
    use MakesStaff, RefreshDatabase;

    public function test_the_seven_customs_stages_are_in_the_customs_order(): void
    {
        $this->assertSame(
            ['Declaration Submitted', 'Pending Valuation', 'Pending Risk', 'Pending Physical Examination', 'Pending Payment', 'Pending Release', 'Released'],
            array_column(Statuses::all(), 'label'),
        );
        foreach (Statuses::all() as $stage) {
            $this->assertNotSame('', $stage['clientText']);
        }
    }

    public function test_a_new_shipment_starts_at_the_first_stage(): void
    {
        $this->assertSame('declaration_submitted', $this->shipment()->status);
    }

    public function test_staff_pick_from_the_new_stages_and_the_old_ones_are_refused(): void
    {
        $s = $this->shipment();
        $this->actingAs($this->staff());
        $this->get("/staff/shipments/{$s->tracking_number}")->assertOk()->assertSee('Pending Physical Examination')->assertDontSee('On hold')->assertDontSee('Duties to pay');
        $this->post("/staff/shipments/{$s->tracking_number}/status", ['status' => 'pending_payment'], ['X-Enhance' => '1'])->assertSee('Status updated.');
        $this->post("/staff/shipments/{$s->tracking_number}/status", ['status' => 'on_hold'], ['X-Enhance' => '1'])->assertSee('Choose a stage.');
    }

    public function test_the_track_page_example_card_shows_all_seven_stages(): void
    {
        $html = $this->get('/track')->assertOk()->getContent();
        foreach (Statuses::all() as $i => $stage) {
            $this->assertStringContainsString($stage['label'], $html);
            $this->assertStringContainsString("sample-step-{$i}", $html);
        }
        $this->assertStringNotContainsString('sample-step-7', $html);
    }

    public function test_the_migration_moves_old_shipments_and_timeline_entries_to_the_new_stages(): void
    {
        $s = $this->shipment();
        DB::table('shipments')->where('id', $s->id)->update(['status' => 'duties_due']);
        DB::table('shipment_events')->insert([
            ['shipment_id' => $s->id, 'status' => 'documents_checked', 'created_at' => now()],
            ['shipment_id' => $s->id, 'status' => 'delivered', 'created_at' => now()],
        ]);

        (require base_path('database/migrations/2026_10_23_000001_replace_clearance_stages.php'))->up();

        $this->assertSame('pending_payment', DB::table('shipments')->where('id', $s->id)->value('status'));
        $this->assertEqualsCanonicalizing(['declaration_submitted', 'pending_valuation', 'released'], DB::table('shipment_events')->where('shipment_id', $s->id)->pluck('status')->all());
    }
}

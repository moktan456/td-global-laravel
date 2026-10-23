<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The clearance stages changed to the seven customs terms (resources/content/statuses.php). Shipments and timeline
 * entries recorded under the old keys are moved to the closest new stage so nothing shows a wrong or empty stage.
 */
return new class extends Migration
{
    private const MAP = [
        'received' => 'declaration_submitted',
        'documents_pending' => 'declaration_submitted',
        'documents_checked' => 'pending_valuation',
        'declaration_lodged' => 'declaration_submitted',
        'duties_due' => 'pending_payment',
        'cleared' => 'pending_release',
        'delivered' => 'released',
        'on_hold' => 'declaration_submitted',
    ];

    public function up(): void
    {
        foreach (self::MAP as $old => $new) {
            DB::table('shipments')->where('status', $old)->update(['status' => $new]);
            DB::table('shipment_events')->where('status', $old)->update(['status' => $new]);
        }
    }

    public function down(): void
    {
        // The old stages are gone for good; the new keys stay.
    }
};

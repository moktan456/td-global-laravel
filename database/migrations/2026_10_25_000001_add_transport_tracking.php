<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transport tracking, an optional second track beside customs clearance. A shipment can follow customs, transport, or both
 * (at least one). Existing shipments become "customs on, transport off", and their timeline entries are customs entries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->boolean('customs_enabled')->default(true)->after('goods');
            $table->boolean('transport_enabled')->default(false)->after('customs_enabled');
            $table->string('transport_status', 30)->nullable()->after('status')->index();

            // Transport details. Everything here is optional. The driver's name and phone are for staff only.
            $table->string('carrier')->nullable();
            $table->string('vehicle_number', 40)->nullable();
            $table->string('pickup_place')->nullable();
            $table->string('destination')->nullable();
            $table->date('expected_delivery')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone', 40)->nullable();

            // Proof of delivery, recorded when the transport track reaches Delivered.
            $table->string('delivered_to')->nullable();
            $table->timestamp('delivered_at')->nullable();
        });

        // A shipment on the transport track only has no customs stage.
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('status', 30)->nullable()->default(null)->change();
        });

        Schema::table('shipment_events', function (Blueprint $table) {
            $table->string('track', 10)->default('customs')->after('shipment_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('shipment_events', function (Blueprint $table) {
            $table->dropColumn('track');
        });
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['customs_enabled', 'transport_enabled', 'transport_status', 'carrier', 'vehicle_number', 'pickup_place', 'destination', 'expected_delivery', 'driver_name', 'driver_phone', 'delivered_to', 'delivered_at']);
        });
    }
};

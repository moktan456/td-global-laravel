<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The tracking application: clients, shipments, their timeline, the notification log and Telegram connect links. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('company')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->unique();            // E.164, e.g. +97517975757
            $table->string('registered_by', 10)->default('staff');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->bigInteger('telegram_chat_id')->nullable()->index();
            $table->timestamp('telegram_linked_at')->nullable();
            $table->boolean('notify_email')->default(true);
            $table->boolean('notify_telegram')->default(true);
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number', 20)->unique();   // TD-YYMM-XXXXXX
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->string('direction', 10);                   // import | export
            $table->string('mode', 10);                        // air | road | rail
            $table->string('goods');
            $table->text('details')->nullable();
            $table->string('status', 30)->default('declaration_submitted')->index();
            $table->string('requested_by', 10)->default('staff');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['client_id', 'created_at']);
        });

        Schema::create('shipment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('status', 30);
            $table->text('note')->nullable();
            $table->boolean('visible_to_client')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['shipment_id', 'created_at']);
        });

        Schema::create('notification_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('audience', 10);                    // client | staff
            $table->string('channel', 10);                     // email | telegram
            $table->string('kind', 30);
            $table->string('status', 10);                      // sent | failed | skipped
            $table->string('error')->nullable();               // why it failed or was skipped; never message text
            $table->timestamp('created_at')->useCurrent();
            $table->index(['shipment_id', 'created_at']);
        });

        // The link a client opens is https://t.me/<bot>?start=<token>. Only a SHA-256 hash of the token is stored.
        Schema::create('telegram_link_tokens', function (Blueprint $table) {
            $table->string('token_hash')->primary();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_link_tokens');
        Schema::dropIfExists('notification_log');
        Schema::dropIfExists('shipment_events');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('clients');
    }
};

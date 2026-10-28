<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Website text edited in the admin panel. One row per content file (`site`, `services`, `faq`...): the saved values
 * sit on top of the defaults that ship in resources/content, so a missing row simply means "use the defaults".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_overrides', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->longText('data');
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_overrides');
    }
};

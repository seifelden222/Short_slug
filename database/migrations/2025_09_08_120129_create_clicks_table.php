<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained('links')->cascadeOnDelete();
            $table->timestamp('occurred_at')->default(now());
            $table->string('ip', 45)->nullable(); // IPv6 max length
            $table->text('ua')->nullable(); // user agent
            $table->string('referrer')->nullable();
            $table->string('country', 2)->nullable(); // ISO country code
            $table->string('idempotency_key')->nullable();
            $table->timestamps();

            // Indexes for performance and uniqueness
            $table->unique(['link_id', 'idempotency_key']);
            $table->index(['link_id', 'occurred_at']);
            $table->index('ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};

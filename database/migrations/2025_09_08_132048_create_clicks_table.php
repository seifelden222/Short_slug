<?php

use App\Models\Click;
use App\Models\Links;
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
            $table->foreignId('link_id')->constrained()->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('country')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('occurred_at')->default(now());
            $table->string('referrer')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->unique(['link_id', 'idempotency_key']);
            $table->timestamps();
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
